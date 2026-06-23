const API_BASE = '/api';

// Tab switching logic
document.querySelectorAll('.nav-item').forEach(button => {
    button.addEventListener('click', () => {
        // Update active nav
        document.querySelectorAll('.nav-item').forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');

        // Update active tab pane
        const tabId = button.getAttribute('data-tab');
        document.querySelectorAll('.tab-pane').forEach(pane => pane.classList.remove('active'));
        document.getElementById(`tab-${tabId}`).classList.add('active');
        
        // Update title
        document.getElementById('page-title').textContent = tabId === 'monitor' ? 'Container Monitoring' : 'System Cleanup Tools';
    });
});

let activeLogStream = null;

// Fetch and display containers
async function fetchContainers() {
    try {
        const response = await fetch(`${API_BASE}/containers`);
        if (!response.ok) throw new Error('Failed to fetch containers');
        const containers = await response.json();
        renderContainers(containers);
        document.getElementById('global-status').textContent = `${containers.length} Containers Tracked`;
        document.getElementById('global-status').className = 'badge success';
    } catch (error) {
        console.error('Error fetching containers:', error);
        document.getElementById('global-status').textContent = 'Error connecting to daemon';
        document.getElementById('global-status').className = 'badge';
    }
}

// Store stats to prevent flickering
const statsCache = {};
const storageCache = {};

async function fetchStats() {
    // Only fetch stats if monitor tab is active
    if (!document.getElementById('tab-monitor').classList.contains('active')) return;

    try {
        const response = await fetch(`${API_BASE}/stats`);
        if (!response.ok) return;
        const statsArray = await response.json();
        
        statsArray.forEach(stat => {
            statsCache[stat.id] = stat;
            const cpuEl = document.getElementById(`cpu-${stat.id}`);
            const memEl = document.getElementById(`mem-${stat.id}`);
            if (cpuEl && memEl) {
                cpuEl.textContent = stat.cpu;
                memEl.textContent = stat.memory;
            }
        });
    } catch (error) {
        console.error('Error fetching stats:', error);
    }
}

async function fetchStorage() {
    if (!document.getElementById('tab-monitor').classList.contains('active')) return;

    try {
        const response = await fetch(`${API_BASE}/storage`);
        if (!response.ok) return;
        const storageArray = await response.json();
        
        storageArray.forEach(stat => {
            storageCache[stat.id] = stat.storage;
            const storageEl = document.getElementById(`storage-${stat.id}`);
            if (storageEl) {
                storageEl.textContent = stat.storage;
            }
        });
    } catch (error) {
        console.error('Error fetching storage:', error);
    }
}

function renderContainers(containers) {
    const listBody = document.getElementById('container-list-body');
    listBody.innerHTML = '';

    containers.forEach(container => {
        const tr = document.createElement('tr');
        
        const name = container.names[0].replace('/', '');
        const stateClass = `status-${container.state.toLowerCase()}`;
        
        const cachedStat = statsCache[container.id] || { cpu: '...', memory: '...' };
        const cachedStorage = storageCache[container.id] || (name.includes('datacontainer') ? '...' : '-');

        tr.innerHTML = `
            <td>
                <div style="font-weight: 500;">${name}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted);">${container.image.split(':')[0]}</div>
            </td>
            <td>
                <span class="badge" style="background: rgba(255,255,255,0.05);">${container.service}</span>
            </td>
            <td>
                <div style="display: flex; align-items: center;">
                    <span class="status-dot ${stateClass}"></span>
                    <span style="text-transform: capitalize;">${container.state}</span>
                </div>
            </td>
            <td class="metrics" id="cpu-${container.id}">${cachedStat.cpu}</td>
            <td class="metrics" id="mem-${container.id}">${cachedStat.memory}</td>
            <td class="metrics" id="storage-${container.id}">${cachedStorage}</td>
            <td>
                <button class="btn-secondary" onclick="viewLogs('${container.id}', '${name}')">View Logs</button>
            </td>
        `;
        listBody.appendChild(tr);
    });
}

function viewLogs(containerId, name) {
    const logsViewer = document.getElementById('logs-viewer');
    const logsTitle = document.getElementById('logs-title');
    
    logsTitle.textContent = `Logs: ${name}`;
    logsViewer.innerHTML = ''; // Clear current

    if (activeLogStream) {
        activeLogStream.close();
    }

    activeLogStream = new EventSource(`${API_BASE}/logs/${containerId}`);
    
    activeLogStream.onmessage = (event) => {
        const line = document.createElement('div');
        line.className = 'log-line';
        try {
            line.textContent = JSON.parse(event.data);
        } catch {
            line.textContent = event.data;
        }
        logsViewer.appendChild(line);
        logsViewer.scrollTop = logsViewer.scrollHeight;
    };

    activeLogStream.onerror = () => {
        activeLogStream.close();
        const line = document.createElement('div');
        line.className = 'log-line';
        line.style.color = 'var(--danger)';
        line.textContent = '[Stream Closed or Error]';
        logsViewer.appendChild(line);
    };
}

document.getElementById('clear-logs').addEventListener('click', () => {
    document.getElementById('logs-viewer').innerHTML = '<div class="empty-state">Logs cleared. Waiting for new lines...</div>';
});

// Toast notification system
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' 
        ? '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'
        : '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
    
    toast.innerHTML = `${icon} <span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Cleanup functions
async function triggerCleanup(type) {
    if (!confirm(`Are you sure you want to perform ${type} cleanup? This action is irreversible.`)) {
        return;
    }

    showToast(`Initiating ${type} cleanup...`, 'success');

    try {
        const response = await fetch(`${API_BASE}/cleanup/${type}`, { method: 'POST' });
        const result = await response.json();

        if (response.ok) {
            showToast(`${type.charAt(0).toUpperCase() + type.slice(1)} cleanup completed successfully.`, 'success');
        } else {
            throw new Error(result.error || 'Cleanup failed');
        }
    } catch (error) {
        console.error(error);
        showToast(`Error: ${error.message}`, 'error');
    }
}

// Initial fetch and set interval
fetchContainers();
setInterval(fetchContainers, 5000);

// Poll stats every 3 seconds
setInterval(fetchStats, 3000);

// Poll storage every 10 seconds
fetchStorage();
setInterval(fetchStorage, 10000);

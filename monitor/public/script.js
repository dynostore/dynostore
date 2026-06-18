document.addEventListener('DOMContentLoaded', () => {
    const containerList = document.getElementById('container-list');
    const logsViewer = document.getElementById('logs-viewer');
    const logsTitle = document.getElementById('logs-title');
    const clearLogsBtn = document.getElementById('clear-logs');
    const globalStatus = document.getElementById('global-status');

    let activeContainerId = null;
    let eventSource = null;
    let autoScroll = true;

    // Fetch containers periodically
    async function fetchContainers() {
        try {
            const res = await fetch('/api/containers');
            const containers = await res.json();
            
            globalStatus.textContent = `${containers.length} Containers`;
            globalStatus.className = 'badge running';
            
            renderContainers(containers);
        } catch (error) {
            console.error('Failed to fetch containers:', error);
            globalStatus.textContent = 'Connection Error';
            globalStatus.className = 'badge exited';
        }
    }

    function renderContainers(containers) {
        // Sort: running first, then exited
        containers.sort((a, b) => {
            if (a.state === 'running' && b.state !== 'running') return -1;
            if (a.state !== 'running' && b.state === 'running') return 1;
            return a.names[0].localeCompare(b.names[0]);
        });

        // Basic DOM diffing alternative: clear and rebuild
        // Better implementation would update nodes in place, but this works for prototype
        containerList.innerHTML = '';

        if(containers.length === 0) {
             const empty = document.createElement('div');
             empty.className = 'empty-state';
             empty.textContent = 'No containers match docker-compose.dev.yml';
             containerList.appendChild(empty);
             return;
        }

        containers.forEach(container => {
            const name = container.names[0].replace(/^\//, ''); // Remove leading slash
            const card = document.createElement('div');
            card.className = `container-card ${activeContainerId === container.id ? 'active' : ''}`;
            card.onclick = () => selectContainer(container.id, name);

            const header = document.createElement('div');
            header.className = 'card-header';
            
            const nameEl = document.createElement('span');
            nameEl.className = 'container-name';
            nameEl.textContent = name;
            nameEl.title = name;

            const badge = document.createElement('span');
            badge.className = `badge ${container.state}`;
            badge.textContent = container.state;

            const imageEl = document.createElement('div');
            imageEl.className = 'container-image';
            imageEl.textContent = container.image;

            header.appendChild(nameEl);
            header.appendChild(badge);
            
            card.appendChild(header);
            card.appendChild(imageEl);

            containerList.appendChild(card);
        });
    }

    function selectContainer(id, name) {
        if (activeContainerId === id) return;
        
        activeContainerId = id;
        logsTitle.textContent = name;
        logsViewer.innerHTML = ''; // Clear current logs
        
        // Update active class on cards
        document.querySelectorAll('.container-card').forEach(card => {
            card.classList.remove('active');
            if (card.querySelector('.container-name').textContent === name) {
                card.classList.add('active');
            }
        });

        startLogStream(id);
    }

    function startLogStream(id) {
        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource(`/api/logs/${id}`);
        
        eventSource.onmessage = (e) => {
            try {
                const logData = JSON.parse(e.data);
                appendLog(logData);
            } catch (err) {
                appendLog(e.data);
            }
        };

        eventSource.onerror = (e) => {
            console.error('SSE Error:', e);
            appendLog('--- Stream Disconnected ---', 'error');
            eventSource.close();
        };

        eventSource.addEventListener('end', () => {
            appendLog('--- Stream Ended ---', 'info');
            eventSource.close();
        });
    }

    function appendLog(text, type = 'normal') {
        const line = document.createElement('div');
        line.className = 'log-line';
        if (type === 'error') line.style.color = 'var(--danger)';
        if (type === 'info') line.style.color = 'var(--accent-color)';
        
        // Simple ANSI color stripping if any
        line.textContent = text.replace(/\u001b\[\d+m/g, ''); 

        logsViewer.appendChild(line);

        if (autoScroll) {
            logsViewer.scrollTop = logsViewer.scrollHeight;
        }
    }

    logsViewer.addEventListener('scroll', () => {
        // Stop auto-scroll if user scrolls up
        const isAtBottom = logsViewer.scrollHeight - logsViewer.scrollTop <= logsViewer.clientHeight + 50;
        autoScroll = isAtBottom;
    });

    clearLogsBtn.addEventListener('click', () => {
        logsViewer.innerHTML = '';
    });

    // Initial fetch and poll
    fetchContainers();
    setInterval(fetchContainers, 3000);
});

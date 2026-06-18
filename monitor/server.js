const express = require('express');
const Docker = require('dockerode');
const fs = require('fs');
const yaml = require('yaml');
const path = require('path');
const cors = require('cors');
const { PassThrough } = require('stream');

const app = express();
const docker = new Docker({ socketPath: '/var/run/docker.sock' });
const PORT = process.env.PORT || 8080;

app.use(cors());
app.use(express.static('public'));

// Parse docker-compose file to get target services
function getTargetServices() {
    try {
        const file = fs.readFileSync(path.join(__dirname, '..', 'docker-compose.dev.yml'), 'utf8');
        const parsed = yaml.parse(file);
        if (parsed && parsed.services) {
            return Object.keys(parsed.services);
        }
        return [];
    } catch (e) {
        console.error('Error parsing docker-compose.dev.yml', e);
        return [];
    }
}

app.get('/api/containers', async (req, res) => {
    try {
        const targetServices = getTargetServices();
        const containers = await docker.listContainers({ all: true });
        
        const monitored = containers.filter(c => {
            const labels = c.Labels || {};
            const serviceName = labels['com.docker.compose.service'];
            return targetServices.includes(serviceName) || targetServices.some(ts => c.Names.some(n => n.includes(ts)));
        });

        const statusList = monitored.map(c => ({
            id: c.Id,
            names: c.Names,
            image: c.Image,
            state: c.State,
            status: c.Status,
            service: c.Labels['com.docker.compose.service'] || 'unknown'
        }));

        res.json(statusList);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

app.get('/api/logs/:id', async (req, res) => {
    const { id } = req.params;
    
    // Set headers for SSE
    res.setHeader('Content-Type', 'text/event-stream');
    res.setHeader('Cache-Control', 'no-cache');
    res.setHeader('Connection', 'keep-alive');
    res.flushHeaders();

    try {
        const container = docker.getContainer(id);
        
        const logStream = await container.logs({
            follow: true,
            stdout: true,
            stderr: true,
            tail: 100
        });

        const passThrough = new PassThrough();
        
        // dockerode handles demuxing the 8-byte header into stdout/stderr cleanly
        container.modem.demuxStream(logStream, passThrough, passThrough);
        
        passThrough.on('data', chunk => {
            const lines = chunk.toString('utf8').split('\n');
            lines.forEach(line => {
                if (line.trim()) {
                    res.write(`data: ${JSON.stringify(line.trim())}\n\n`);
                }
            });
        });

        logStream.on('end', () => {
            res.write('event: end\ndata: stream closed\n\n');
            res.end();
        });

        req.on('close', () => {
            res.end();
            // In a real app we might try to destroy logStream here if dockerode supports it
        });

    } catch (error) {
        res.write(`event: error\ndata: ${JSON.stringify(error.message)}\n\n`);
        res.end();
    }
});

app.listen(PORT, () => {
    console.log(`Monitor service listening on port ${PORT}`);
});

<style>
/* Compact Server Health Monitor - Mobile First Design */
:root {
    --health-bg: #0f172a;
    --health-card-bg: #1e293b;
    --health-border: #334155;
    --health-success: #10b981;
    --health-warning: #f59e0b;
    --health-danger: #ef4444;
    --health-info: #3b82f6;
    --health-text: #e2e8f0;
    --health-text-dim: #94a3b8;
}

/* Mobile First Approach */
.server-health-section {
    background: var(--health-bg);
    border-radius: 16px;
    padding: 12px;
    margin-bottom: 20px;
    border: 1px solid var(--health-border);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.server-health-section.minimized {
    padding: 10px 12px;
}

.server-health-section.minimized .health-content {
    display: none;
}

/* Header - Always Visible */
.server-health-header {
    display: flex;
    flex-direction: column;
    gap: 10px;
    cursor: pointer;
}

@media (min-width: 640px) {
    .server-health-header {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
    }
}

.header-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.header-left h4 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    color: #10b981;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.header-left h4 i {
    font-size: 1rem;
}

/* Mini Stats - Horizontal Scroll on Mobile */
.health-mini-stats {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding: 2px 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}

.health-mini-stats::-webkit-scrollbar {
    display: none;
}

.mini-stat {
    display: flex;
    align-items: center;
    gap: 4px;
    background: rgba(255,255,255,0.05);
    padding: 4px 10px;
    border-radius: 20px;
    border: 1px solid var(--health-border);
    font-size: 0.7rem;
    white-space: nowrap;
}

.mini-stat i {
    font-size: 0.7rem;
}

.mini-stat .label {
    color: var(--health-text-dim);
}

.mini-stat .value {
    color: var(--health-text);
    font-weight: 600;
}

.mini-stat .value.good { color: var(--health-success); }
.mini-stat .value.warning { color: var(--health-warning); }
.mini-stat .value.danger { color: var(--health-danger); }

.header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    justify-content: flex-end;
}

.health-status-badge {
    padding: 3px 8px;
    border-radius: 20px;
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(16,185,129,0.1);
    border: 1px solid var(--health-success);
    color: var(--health-success);
    white-space: nowrap;
}

.health-status-badge.warning {
    background: rgba(245,158,11,0.1);
    border-color: var(--health-warning);
    color: var(--health-warning);
}

.health-status-badge.danger {
    background: rgba(239,68,68,0.1);
    border-color: var(--health-danger);
    color: var(--health-danger);
}

.health-toggle-btn, .health-refresh-btn {
    background: rgba(255,255,255,0.1);
    border: 1px solid var(--health-border);
    color: var(--health-text);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.8rem;
}

.health-toggle-btn:hover, .health-refresh-btn:hover {
    background: var(--health-success);
    color: var(--health-bg);
}

.health-refresh-btn:hover {
    transform: rotate(90deg);
}

/* Content Area */
.health-content {
    margin-top: 15px;
    transition: all 0.3s ease;
}

/* Cards Row - Horizontal Scroll on Mobile */
.health-cards-row {
    display: flex;
    gap: 10px;
    overflow-x: auto;
    padding: 5px 0 10px 0;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: var(--health-success) var(--health-border);
}

.health-cards-row::-webkit-scrollbar {
    height: 4px;
}

.health-cards-row::-webkit-scrollbar-track {
    background: var(--health-border);
    border-radius: 4px;
}

.health-cards-row::-webkit-scrollbar-thumb {
    background: var(--health-success);
    border-radius: 4px;
}

/* Compact Cards - Fixed Width for Horizontal Scroll */
.health-compact-card {
    flex: 0 0 200px;
    background: var(--health-card-bg);
    border-radius: 14px;
    padding: 12px;
    border: 1px solid var(--health-border);
    transition: all 0.3s ease;
}

@media (min-width: 640px) {
    .health-compact-card {
        flex: 0 0 220px;
    }
}

@media (min-width: 1024px) {
    .health-cards-row {
        overflow-x: visible;
        flex-wrap: wrap;
    }
    
    .health-compact-card {
        flex: 1 1 calc(25% - 10px);
        min-width: 180px;
    }
}

.health-compact-card:hover {
    transform: translateY(-2px);
    border-color: var(--health-success);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

.card-header-compact {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--health-border);
}

.card-header-compact i {
    font-size: 0.9rem;
    color: var(--health-success);
}

.card-header-compact span {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--health-text);
    text-transform: uppercase;
    flex: 1;
}

.card-status-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--health-success);
}

.card-status-dot.good { background: var(--health-success); }
.card-status-dot.warning { background: var(--health-warning); }
.card-status-dot.danger { background: var(--health-danger); }

.card-metrics-compact {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.metric-mini .metric-label {
    font-size: 0.55rem;
    color: var(--health-text-dim);
    text-transform: uppercase;
    margin-bottom: 2px;
}

.metric-mini .metric-value {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--health-text);
    font-family: 'Courier New', monospace;
}

.metric-mini .metric-value.good { color: var(--health-success); }
.metric-mini .metric-value.warning { color: var(--health-warning); }
.metric-mini .metric-value.danger { color: var(--health-danger); }

/* Progress Bar */
.progress-mini {
    height: 2px;
    background: rgba(255,255,255,0.1);
    border-radius: 2px;
    margin-top: 3px;
    overflow: hidden;
}

.progress-mini-bar {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease;
}

.progress-mini-bar.good { background: var(--health-success); }
.progress-mini-bar.warning { background: var(--health-warning); }
.progress-mini-bar.danger { background: var(--health-danger); }

/* Details Row */
.health-details-row {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 12px;
}

@media (min-width: 640px) {
    .health-details-row {
        flex-direction: row;
    }
}

.health-detail-card {
    flex: 1;
    background: var(--health-card-bg);
    border-radius: 14px;
    padding: 12px;
    border: 1px solid var(--health-border);
}

.detail-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid var(--health-border);
}

.detail-header i {
    font-size: 1rem;
    color: var(--health-info);
}

.detail-header span {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--health-text);
    text-transform: uppercase;
    flex: 1;
}

.detail-header .status-badge {
    padding: 2px 6px;
    border-radius: 20px;
    font-size: 0.6rem;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(16,185,129,0.1);
    border: 1px solid var(--health-success);
    color: var(--health-success);
    white-space: nowrap;
}

.detail-header .status-badge.warning {
    background: rgba(245,158,11,0.1);
    border-color: var(--health-warning);
    color: var(--health-warning);
}

.detail-header .status-badge.danger {
    background: rgba(239,68,68,0.1);
    border-color: var(--health-danger);
    color: var(--health-danger);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.detail-item .detail-label {
    font-size: 0.55rem;
    color: var(--health-text-dim);
    text-transform: uppercase;
    margin-bottom: 2px;
}

.detail-item .detail-value {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--health-text);
    font-family: 'Courier New', monospace;
}

/* Last Updated */
.text-right {
    text-align: right;
    margin-top: 10px;
}

.text-right small {
    color: var(--health-text-dim);
    font-size: 0.6rem;
}

/* Loading State */
.health-loading {
    text-align: center;
    padding: 20px;
}

.loading-spinner-small {
    width: 24px;
    height: 24px;
    border: 2px solid var(--health-border);
    border-top-color: var(--health-success);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<div class="server-health-section" id="serverHealthSection">
    <!-- Header - Always Visible -->
    <div class="server-health-header" onclick="toggleHealthSection()">
        <div class="header-left">
            <h4>
                <i class="fas fa-server"></i>
                SERVER
            </h4>
            <div class="health-mini-stats" id="healthMiniStats">
                <div class="mini-stat">
                    <i class="fas fa-microchip"></i>
                    <span class="label">CPU:</span>
                    <span class="value" id="miniCpu">45%</span>
                </div>
                <div class="mini-stat">
                    <i class="fas fa-memory"></i>
                    <span class="label">RAM:</span>
                    <span class="value" id="miniRam">25%</span>
                </div>
                <div class="mini-stat">
                    <i class="fas fa-hdd"></i>
                    <span class="label">DISK:</span>
                    <span class="value" id="miniDisk">20%</span>
                </div>
                <div class="mini-stat">
                    <i class="fas fa-database"></i>
                    <span class="label">DB:</span>
                    <span class="value" id="miniDb">2.3ms</span>
                </div>
            </div>
        </div>
        <div class="header-right">
            <span class="health-status-badge" id="overallHealthBadge">
                <i class="fas fa-circle mr-1"></i> OK
            </span>
            <div class="health-refresh-btn" onclick="refreshHealthData(event)">
                <i class="fas fa-sync-alt"></i>
            </div>
            <div class="health-toggle-btn" id="healthToggleBtn">
                <i class="fas fa-chevron-up"></i>
            </div>
        </div>
    </div>

    <!-- Content - Minimizable -->
    <div class="health-content" id="healthContent">
        <!-- Cards Row - Horizontal Scroll -->
        <div class="health-cards-row" id="healthCardsRow">
            <!-- Cards will be loaded dynamically -->
            <div class="health-compact-card">
                <div class="card-header-compact">
                    <i class="fas fa-server"></i>
                    <span>SERVER</span>
                    <span class="card-status-dot good"></span>
                </div>
                <div class="card-metrics-compact">
                    <div class="metric-mini">
                        <div class="metric-label">CPU</div>
                        <div class="metric-value good">45%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-bar good" style="width: 45%"></div>
                        </div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">RAM</div>
                        <div class="metric-value good">25%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-bar good" style="width: 25%"></div>
                        </div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">DISK</div>
                        <div class="metric-value good">20%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-bar good" style="width: 20%"></div>
                        </div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">UPTIME</div>
                        <div class="metric-value">15d</div>
                    </div>
                </div>
            </div>
            
            <div class="health-compact-card">
                <div class="card-header-compact">
                    <i class="fas fa-database"></i>
                    <span>DATABASE</span>
                    <span class="card-status-dot good"></span>
                </div>
                <div class="card-metrics-compact">
                    <div class="metric-mini">
                        <div class="metric-label">RESPONSE</div>
                        <div class="metric-value">2.3ms</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">CONN</div>
                        <div class="metric-value">12/150</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">SLOW Q</div>
                        <div class="metric-value">0</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">BUFFER</div>
                        <div class="metric-value">128MB</div>
                    </div>
                </div>
            </div>
            
            <div class="health-compact-card">
                <div class="card-header-compact">
                    <i class="fab fa-redis"></i>
                    <span>REDIS</span>
                    <span class="card-status-dot warning"></span>
                </div>
                <div class="card-metrics-compact">
                    <div class="metric-mini">
                        <div class="metric-label">VERSION</div>
                        <div class="metric-value">6.2.6</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">MEMORY</div>
                        <div class="metric-value">128MB</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">CLIENTS</div>
                        <div class="metric-value">45</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">HIT RATIO</div>
                        <div class="metric-value warning">65%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-bar warning" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="health-compact-card">
                <div class="card-header-compact">
                    <i class="fas fa-bolt"></i>
                    <span>CACHE</span>
                    <span class="card-status-dot good"></span>
                </div>
                <div class="card-metrics-compact">
                    <div class="metric-mini">
                        <div class="metric-label">DRIVER</div>
                        <div class="metric-value">redis</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">READ</div>
                        <div class="metric-value">0.2ms</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">WRITE</div>
                        <div class="metric-value">0.3ms</div>
                    </div>
                    <div class="metric-mini">
                        <div class="metric-label">EFF</div>
                        <div class="metric-value good">98%</div>
                        <div class="progress-mini">
                            <div class="progress-mini-bar good" style="width: 98%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Redis & Security Row -->
        <div class="health-details-row">
            <!-- Redis Status -->
            <div class="health-detail-card">
                <div class="detail-header">
                    <i class="fab fa-redis"></i>
                    <span>REDIS</span>
                    <span class="status-badge" id="redisStatusBadge">ACTIVE</span>
                </div>
                <div class="detail-grid" id="redisCompactDetails">
                    <div class="detail-item">
                        <div class="detail-label">Version</div>
                        <div class="detail-value" id="redisVersion">6.2.6</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Memory</div>
                        <div class="detail-value" id="redisMemory">128MB</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Clients</div>
                        <div class="detail-value" id="redisClients">45</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Hit Ratio</div>
                        <div class="detail-value" id="redisHitRatio">65%</div>
                    </div>
                </div>
            </div>

            <!-- Security Status -->
            <div class="health-detail-card">
                <div class="detail-header">
                    <i class="fas fa-shield-alt"></i>
                    <span>SECURITY</span>
                    <span class="status-badge good" id="securityStatusBadge">LOW</span>
                </div>
                <div class="detail-grid" id="securityCompactDetails">
                    <div class="detail-item">
                        <div class="detail-label">Risk Level</div>
                        <div class="detail-value" id="securityRisk">LOW</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Scanned</div>
                        <div class="detail-value" id="securityScanned">1,250</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Suspicious</div>
                        <div class="detail-value" id="securitySuspicious">0</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Modified</div>
                        <div class="detail-value" id="securityModified">3</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Last Updated -->
        <div class="text-right">
            <small>
                <i class="far fa-clock mr-1"></i>
                <span id="lastUpdated">Just now</span>
            </small>
        </div>
    </div>
</div>

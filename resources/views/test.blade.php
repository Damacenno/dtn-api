<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador Avançado de Rede DTN - Roteamento Epidêmico</title>
    <!-- Tailwind CSS para Estilização Moderna estilo Dashboard Laravel -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Chart.js para Métricas de Desempenho da Rede em Tempo Real -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- FontAwesome para Ícones -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #1e293b;
        }
        ::-webkit-scrollbar-thumb {
            background: #475569;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        .canvas-container {
            position: relative;
            width: 100%;
            height: 520px;
            background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);
            overflow: hidden;
            border-radius: 0.75rem;
        }

        canvas {
            display: block;
            width: 100%;
            height: 100%;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            font-weight: 600;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">

    <!-- Top Navigation Bar / Header Estilo Dashboard Laravel -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50 px-6 py-3 flex justify-between items-center">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-[#fa253b] fa-network-wired text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold text-white leading-tight">Simulador DTN Epidêmico</h1>
                <p class="text-xs text-slate-400">Delay-Tolerant Networking & Store-Carry-Forward Protocol</p>
            </div>
        </div>

        <!-- Controls Toolbar -->
        <div class="flex items-center gap-3">
            <button id="btnPlayPause" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-lg font-medium text-sm flex items-center gap-2 transition shadow-md">
                <i class="fa-solid fa-play" id="iconPlay"></i> <span id="textPlay">Iniciar</span>
            </button>
            <button id="btnStep" class="bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 px-3 py-2 rounded-lg font-medium text-sm transition">
                <i class="fa-solid fa-step-forward"></i> Step
            </button>
            <button id="btnReset" class="bg-rose-950/60 hover:bg-rose-900/80 text-rose-300 border border-rose-800/50 px-3 py-2 rounded-lg font-medium text-sm transition">
                <i class="fa-solid fa-rotate-left"></i> Reset
            </button>

            <div class="h-6 w-px bg-slate-800 mx-1"></div>

            <label class="text-xs text-slate-400 flex items-center gap-2">
                Speed:
                <select id="simSpeed" class="bg-slate-800 text-white text-xs rounded border border-slate-700 px-2 py-1 focus:outline-none">
                    <option value="0.5">0.5x</option>
                    <option value="1" selected>1.0x</option>
                    <option value="2">2.0x</option>
                    <option value="5">5.0x</option>
                </select>
            </label>
        </div>
    </header>

    <!-- Main Container Layout Grid -->
    <main class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-6 p-6 max-w-[1800px] mx-auto w-full">

        <!-- LEFT PANEL: Canvas & Active Metrics (8 Cols) -->
        <div class="lg:col-span-8 flex flex-col gap-6">

            <!-- Simulation Canvas Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl flex flex-col">
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-2">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="font-semibold text-slate-200 text-sm">Ambiente Geográfico de Simulação (Canvas 2D)</h2>
                    </div>
                    <div class="flex items-center gap-4 text-xs text-slate-400">
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-500 inline-block"></span> Nó Móvel</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Gateway (Internet)</span>
                        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full border border-dashed border-indigo-400 inline-block"></span> Raio Rádio</span>
                    </div>
                </div>

                <!-- Canvas Visual Area -->
                <div class="canvas-container border border-slate-800" id="canvasContainer">
                    <canvas id="simCanvas"></canvas>
                </div>

                <!-- Live Quick Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 text-slate-300">
                    <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80">
                        <p class="text-xs text-slate-400 font-medium">Taxa de Entrega</p>
                        <p class="text-xl font-bold text-emerald-400 mt-0.5" id="metricDeliveryRate">0%</p>
                    </div>
                    <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80">
                        <p class="text-xs text-slate-400 font-medium">Mensagens Criadas / Entregues</p>
                        <p class="text-xl font-bold text-indigo-400 mt-0.5" id="metricCreatedDelivered">0 / 0</p>
                    </div>
                    <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80">
                        <p class="text-xs text-slate-400 font-medium">Overhead de Cópias (Replicações)</p>
                        <p class="text-xl font-bold text-amber-400 mt-0.5" id="metricOverhead">0.0x</p>
                    </div>
                    <div class="bg-slate-950/60 p-3 rounded-lg border border-slate-800/80">
                        <p class="text-xs text-slate-400 font-medium">Tempo Médio de Entrega</p>
                        <p class="text-xl font-bold text-cyan-400 mt-0.5" id="metricAvgLatency">0.0s</p>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Chart 1: Delivery & Replications over time -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Desempenho da Rede ao Longo do Tempo</h3>
                    <div class="h-48">
                        <canvas id="chartPerformance"></canvas>
                    </div>
                </div>

                <!-- Chart 2: Buffer Utilization per Node -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl">
                    <h3 class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Ocupação de Buffer por Nó (Mensagens)</h3>
                    <div class="h-48">
                        <canvas id="chartBuffers"></canvas>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT PANEL: Settings, Generator, Node Inspector & Logs (4 Cols) -->
        <div class="lg:col-span-4 flex flex-col gap-6">

            <!-- Control Tabs -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl flex flex-col gap-4">
                
                <!-- Parameters Form -->
                <div>
                    <h2 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-indigo-400"></i> Parâmetros do Algoritmo DTN
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-3 text-xs">
                        <div>
                            <label class="text-slate-400 block mb-1">Raio de Alcance (px)</label>
                            <input type="number" id="paramRadioRange" value="85" min="30" max="250" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-slate-400 block mb-1">Velocidade dos Nós</label>
                            <input type="number" id="paramSpeed" value="1.5" min="0.1" max="10" step="0.1" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-slate-400 block mb-1">TTL Padrão (s)</label>
                            <input type="number" id="paramTTL" value="45" min="5" max="300" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="text-slate-400 block mb-1">Capacidade Buffer (por Nó)</label>
                            <input type="number" id="paramBufferSize" value="5" min="1" max="50" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Toggle Options -->
                    <div class="mt-3 flex flex-col gap-2 text-xs">
                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                            <input type="checkbox" id="paramAntiEntropy" checked class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
                            Utilizar Troca de Vetor de Sumário (Anti-Entropy)
                        </label>
                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                            <input type="checkbox" id="paramAckPurging" checked class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
                            Limpeza por ACK (Apagar cópias após entrega no Gateway)
                        </label>
                        <label class="flex items-center gap-2 text-slate-300 cursor-pointer">
                            <input type="checkbox" id="paramAutoMobility" checked class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
                            Movimentação Automática (Random Waypoint)
                        </label>
                    </div>
                </div>

                <div class="h-px bg-slate-800 my-1"></div>

                <!-- Create Message Panel -->
                <div>
                    <h2 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-emerald-400"></i> Criar Novo Bundle (Mensagem)
                    </h2>

                    <div class="flex flex-col gap-3 text-xs">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="text-slate-400 block mb-1">Nó Origem</label>
                                <select id="msgOrigin" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none"></select>
                            </div>
                            <div>
                                <label class="text-slate-400 block mb-1">Nó Destino</label>
                                <select id="msgDestination" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none"></select>
                            </div>
                        </div>

                        <div>
                            <label class="text-slate-400 block mb-1">Conteúdo da Mensagem</label>
                            <input type="text" id="msgContent" value="Alerta de Emergência DTN" class="w-full bg-slate-800 border border-slate-700 text-white rounded p-2 focus:border-indigo-500 focus:outline-none">
                        </div>

                        <button id="btnCreateMsg" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-medium p-2 rounded-lg transition text-xs flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i> Injetar Mensagem na Rede
                        </button>
                    </div>
                </div>

                <div class="h-px bg-slate-800 my-1"></div>

                <!-- Node Management Panel -->
                <div>
                    <h2 class="text-sm font-semibold text-white mb-3 flex items-center justify-between">
                        <span class="flex items-center gap-2"><i class="fa-solid fa-laptop-code text-cyan-400"></i> Gestão de Nós</span>
                        <button id="btnAddNode" class="bg-indigo-600/80 hover:bg-indigo-600 text-white text-xs px-2.5 py-1 rounded transition">
                            + Add Nó
                        </button>
                    </h2>

                    <!-- Nodes Quick List / Control -->
                    <div id="nodesList" class="max-h-36 overflow-y-auto space-y-1.5 pr-1"></div>
                </div>

            </div>

            <!-- Event Console / Audit Log -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-xl flex-1 flex flex-col h-80">
                <div class="flex justify-between items-center mb-2">
                    <h2 class="text-sm font-semibold text-white flex items-center gap-2">
                        <i class="fa-solid fa-terminal text-amber-400"></i> Console de Eventos da Rede
                    </h2>
                    <button id="btnClearLogs" class="text-xs text-slate-500 hover:text-slate-300 transition">
                        Limpar Log
                    </button>
                </div>

                <div id="logConsole" class="flex-1 bg-slate-950 rounded-lg border border-slate-800 p-3 font-mono text-xs overflow-y-auto space-y-1">
                    <div class="text-slate-500">[00:00.0] Sistema de simulação DTN inicializado.</div>
                </div>
            </div>

        </div>

    </main>

    <!-- Modal / Drawer - Bundle Details Inspector -->
    <div id="bundleModal" class="hidden fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-lg w-full p-6 shadow-2xl flex flex-col gap-4">
            <div class="flex justify-between items-start border-b border-slate-800 pb-3">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        <i class="fa-solid fa-box text-indigo-400"></i> Detalhes do Bundle (Mensagem)
                    </h3>
                    <p class="text-xs text-slate-400" id="modalBundleId">ID: -</p>
                </div>
                <button id="btnCloseModal" class="text-slate-400 hover:text-white text-lg">&times;</button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                    <p class="text-slate-400 mb-1">Conteúdo:</p>
                    <p class="text-white font-medium text-sm" id="modalBundleContent">-</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-400">Origem:</span> <span class="font-bold text-indigo-400" id="modalBundleOrigin">-</span>
                    </div>
                    <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-400">Destino:</span> <span class="font-bold text-emerald-400" id="modalBundleDest">-</span>
                    </div>
                    <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-400">Status:</span> <span class="font-bold" id="modalBundleStatus">-</span>
                    </div>
                    <div class="bg-slate-950 p-2.5 rounded-lg border border-slate-800">
                        <span class="text-slate-400">Cópias Ativas:</span> <span class="font-bold text-amber-400" id="modalBundleCopies">-</span>
                    </div>
                </div>

                <div>
                    <p class="text-slate-400 mb-1">Caminho / Hops de Transmissão (Histórico):</p>
                    <div id="modalBundleHops" class="bg-slate-950 p-3 rounded-lg border border-slate-800 space-y-1 font-mono max-h-32 overflow-y-auto"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SIMULATION SCRIPT LOGIC -->
    <script>
        /**
         * ====================================================================
         * SIMULADOR DTN - ROTEAMENTO EPIDÊMICO (EPIDEMIC ROUTING PROTOCOL)
         * ARQUITETURA STORE-CARRY-AND-FORWARD COM ANT-ENTROPY VECTORS
         * ====================================================================
         */

        // State Global da Simulação
        const state = {
            isRunning: false,
            simTime: 0, // Segundos simulados
            speedMultiplier: 1.0,
            nodes: [],
            bundles: [], // Todas as mensagens criadas
            deliveredBundleIds: new Set(), // Acks Globais / Confirmações
            logs: [],
            stats: {
                created: 0,
                delivered: 0,
                replications: 0,
                totalLatency: 0
            },
            selectedNodeForDrag: null,
            animFrameId: null
        };

        // Elementos DOM
        const canvas = document.getElementById('simCanvas');
        const ctx = canvas.getContext('2d');
        const canvasContainer = document.getElementById('canvasContainer');

        // Charts Instances
        let chartPerformance, chartBuffers;

        /**
         * CLASSE DO NÓ (DISPOSITIVO / GATEWAY)
         */
        class Node {
            constructor(id, x, y, isGateway = false) {
                this.id = id;
                this.x = x;
                this.y = y;
                this.isGateway = isGateway;
                this.hasInternet = isGateway; // Gateways possuem conexão contínua
                this.isActive = true; // Status da bateria/ligado
                this.buffer = []; // Lista de IDs de bundles armazenados
                
                // Mobilidade (Random Waypoint)
                this.targetX = x;
                this.targetY = y;
                this.speed = parseFloat(document.getElementById('paramSpeed').value);
                this.pauseTimer = 0;

                // Animações visuais
                this.pulseRadius = 0;
            }

            update(dt, width, height, autoMobility) {
                if (!this.isActive) return;

                // Animação visual do rádio
                this.pulseRadius = (this.pulseRadius + dt * 40) % parseFloat(document.getElementById('paramRadioRange').value);

                // Gateways são estáticos por padrão
                if (this.isGateway || !autoMobility) return;

                if (this.pauseTimer > 0) {
                    this.pauseTimer -= dt;
                    return;
                }

                // Distância até o destino atual
                const dx = this.targetX - this.x;
                const dy = this.targetY - this.y;
                const dist = Math.hypot(dx, dy);

                const currentSpeed = parseFloat(document.getElementById('paramSpeed').value) * 30;

                if (dist < 5) {
                    // Escolhe novo destino dentro da tela
                    this.pauseTimer = Math.random() * 2 + 1; // Pausa entre 1s e 3s
                    const margin = 40;
                    this.targetX = margin + Math.random() * (width - margin * 2);
                    this.targetY = margin + Math.random() * (height - margin * 2);
                } else {
                    // Move em direção ao waypoint
                    this.x += (dx / dist) * currentSpeed * dt;
                    this.y += (dy / dist) * currentSpeed * dt;
                }
            }
        }

        /**
         * CLASSE DO BUNDLE (MENSAGEM / PACOTE DTN)
         */
        class Bundle {
            constructor(id, originId, destId, content, ttl) {
                this.id = id;
                this.originId = originId;
                this.destId = destId; // ID do nó destino ou "GATEWAY"
                this.content = content;
                this.createdAt = state.simTime;
                this.ttl = ttl; // Time to live em segundos
                this.delivered = false;
                this.deliveredAt = null;
                this.hops = [{ nodeId: originId, time: state.simTime }]; // Histórico
            }

            isExpired() {
                return (state.simTime - this.createdAt) >= this.ttl;
            }
        }

        /**
         * INICIALIZAÇÃO DA REDE E CENÁRIO PADRÃO
         */
        function initSimulation() {
            // Ajustar tamanho do canvas
            resizeCanvas();

            state.nodes = [
                new Node('Nó A', canvas.width * 0.15, canvas.height * 0.25),
                new Node('Nó B', canvas.width * 0.25, canvas.height * 0.70),
                new Node('Nó C', canvas.width * 0.50, canvas.height * 0.35),
                new Node('Nó D', canvas.width * 0.65, canvas.height * 0.75),
                new Node('GATEWAY', canvas.width * 0.85, canvas.height * 0.50, true)
            ];

            state.bundles = [];
            state.deliveredBundleIds.clear();
            state.simTime = 0;
            state.stats = { created: 0, delivered: 0, replications: 0, totalLatency: 0 };

            initCharts();
            updateUI();
            logEvent('Cenário de rede inicializado com 4 nós móveis e 1 Gateway.');
        }

        function resizeCanvas() {
            const rect = canvasContainer.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
        }

        window.addEventListener('resize', () => {
            resizeCanvas();
            renderCanvas();
        });

        /**
         * MOTOR PRINCIPAL DE SIMULAÇÃO (LOOP DTN)
         */
        let lastTimestamp = performance.now();

        function simulationLoop(now) {
            const deltaReal = (now - lastTimestamp) / 1000;
            lastTimestamp = now;

            if (state.isRunning) {
                const dt = deltaReal * state.speedMultiplier;
                state.simTime += dt;

                // 1. Atualizar posições dos nós
                const autoMobility = document.getElementById('paramAutoMobility').checked;
                state.nodes.forEach(node => node.update(dt, canvas.width, canvas.height, autoMobility));

                // 2. Executar Algoritmo DTN Epidêmico (Contatos & Réplicas)
                processDTNContacts();

                // 3. Processar expiração por TTL e Limpeza por ACK
                processTTLAndPurging();

                // 4. Atualizar Métricas e Gráficos periodicamente
                updateMetrics();
            }

            // Renderizar a cena visual
            renderCanvas();

            state.animFrameId = requestAnimationFrame(simulationLoop);
        }

        /**
         * ALGORITMO EPIDÊMICO - TROCA DE VETORES DE SUMÁRIO & REPLICAÇÃO
         */
        function processDTNContacts() {
            const radioRange = parseFloat(document.getElementById('paramRadioRange').value);
            const useAntiEntropy = document.getElementById('paramAntiEntropy').checked;
            const maxBuffer = parseInt(document.getElementById('paramBufferSize').value);

            // Verificar todos os pares de nós em alcance de comunicação rádio
            for (let i = 0; i < state.nodes.length; i++) {
                for (let j = i + 1; j < state.nodes.length; j++) {
                    const nodeA = state.nodes[i];
                    const nodeB = state.nodes[j];

                    if (!nodeA.isActive || !nodeB.isActive) continue;

                    const dist = Math.hypot(nodeA.x - nodeB.x, nodeA.y - nodeB.y);

                    // Entrar em raio de contato (Oportunidade de Contato DTN)
                    if (dist <= radioRange) {
                        
                        // Desenhar linha de conexão temporária no Canvas
                        drawContactLine(nodeA, nodeB);

                        // Executar Anti-Entropy (Troca bidirecional de mensagens)
                        exchangeEpidemicBundles(nodeA, nodeB, useAntiEntropy, maxBuffer);
                        exchangeEpidemicBundles(nodeB, nodeA, useAntiEntropy, maxBuffer);
                    }
                }
            }

            // Verificar entrega direta de nós que possuem internet/Gateway
            state.nodes.forEach(node => {
                if (node.isActive && node.hasInternet) {
                    processGatewayDelivery(node);
                }
            });
        }

        /**
         * TRANSFERÊNCIA DE REPLICAÇÃO EPIDÊMICA ENTRE DOIS NÓS (A -> B)
         */
        function exchangeEpidemicBundles(sender, receiver, useAntiEntropy, maxBuffer) {
            // Obter bundles do remetente
            const senderBundles = [...sender.buffer];

            for (const bundleId of senderBundles) {
                const bundle = state.bundles.find(b => b.id === bundleId);
                if (!bundle || bundle.isExpired()) continue;

                // ANTI-ENTROPY: Se o receptor já possui a mensagem, pula
                if (useAntiEntropy && receiver.buffer.includes(bundleId)) {
                    continue;
                }

                // Se o receptor já entregou ou se o bundle foi confirmado no Ack global
                if (state.deliveredBundleIds.has(bundleId) && document.getElementById('paramAckPurging').checked) {
                    continue;
                }

                // Verificar Limite do Buffer do Receptor (Store-Carry-and-Forward)
                if (receiver.buffer.length >= maxBuffer) {
                    // Buffer cheio: Política DTN Drop/Reject
                    continue;
                }

                // EFETUAR REPLICAÇÃO EPIDÊMICA (CÓPIA)
                if (!receiver.buffer.includes(bundleId)) {
                    receiver.buffer.push(bundleId);
                    state.stats.replications++;

                    // Registrar Hop na história da mensagem
                    bundle.hops.push({
                        nodeId: receiver.id,
                        time: state.simTime
                    });

                    logEvent(
                        `<span class="text-indigo-400">${sender.id}</span> ➔ <span class="text-cyan-400">${receiver.id}</span>: Replicou bundle <b>${bundleId.slice(0, 6)}...</b>`,
                        'transfer'
                    );
                }
            }
        }

        /**
         * PROCESSAMENTO DE ENTREGA NO GATEWAY / DESTINO FINAL
         */
        function processGatewayDelivery(gatewayNode) {
            const ackPurge = document.getElementById('paramAckPurging').checked;

            [...gatewayNode.buffer].forEach(bundleId => {
                const bundle = state.bundles.find(b => b.id === bundleId);

                if (bundle && !bundle.delivered) {
                    bundle.delivered = true;
                    bundle.deliveredAt = state.simTime;
                    state.deliveredBundleIds.add(bundleId);
                    state.stats.delivered++;

                    const latency = bundle.deliveredAt - bundle.createdAt;
                    state.stats.totalLatency += latency;

                    logEvent(
                        `🎉 <b class="text-emerald-400">ENTREGA CONFIRMADA!</b> Bundle <b>${bundleId.slice(0, 6)}...</b> entregue no ${gatewayNode.id} em ${latency.toFixed(1)}s!`,
                        'success'
                    );
                }
            });

            // Se o ACK Purging estiver ativado, limpa do buffer do Gateway para dar espaço
            if (ackPurge) {
                gatewayNode.buffer = gatewayNode.buffer.filter(id => !state.deliveredBundleIds.has(id));
            }
        }

        /**
         * GERENCIAMENTO DE EXPIRAÇÃO DE TTL E ACK PURGING GLOBAL
         */
        function processTTLAndPurging() {
            const ackPurge = document.getElementById('paramAckPurging').checked;

            state.nodes.forEach(node => {
                node.buffer = node.buffer.filter(bundleId => {
                    const bundle = state.bundles.find(b => b.id === bundleId);

                    if (!bundle) return false;

                    // Remover se expirou por TTL
                    if (bundle.isExpired()) {
                        logEvent(`⌛ Bundle <b>${bundleId.slice(0, 6)}...</b> expirou no buffer do ${node.id} (TTL atingido).`, 'warning');
                        return false;
                    }

                    // Purging por ACK Global: Se a mensagem já foi entregue ao Gateway, remove dos nós intermediários
                    if (ackPurge && state.deliveredBundleIds.has(bundleId) && !node.isGateway) {
                        return false;
                    }

                    return true;
                });
            });
        }

        /**
         * CRIAÇÃO MANUAL DE MENSAGENS / BUNDLES
         */
        function createBundle() {
            const originId = document.getElementById('msgOrigin').value;
            const destId = document.getElementById('msgDestination').value;
            const content = document.getElementById('msgContent').value.trim() || 'Sem Conteúdo';
            const ttl = parseFloat(document.getElementById('paramTTL').value);

            const originNode = state.nodes.find(n => n.id === originId);

            if (!originNode || !originNode.isActive) {
                logEvent('Erro: O nó de origem está inativo ou indisponível.', 'error');
                return;
            }

            const maxBuffer = parseInt(document.getElementById('paramBufferSize').value);
            if (originNode.buffer.length >= maxBuffer) {
                logEvent(`Buffer do ${originNode.id} está cheio! Não foi possível armazenar a nova mensagem.`, 'warning');
                return;
            }

            const bundleId = 'b-' + Math.random().toString(36).substring(2, 9);
            const bundle = new Bundle(bundleId, originId, destId, content, ttl);

            state.bundles.push(bundle);
            originNode.buffer.push(bundleId);
            state.stats.created++;

            logEvent(`✉️ <span class="text-emerald-400">${originId}</span> gerou novo bundle <b>${bundleId}</b> para ${destId}.`, 'create');
            updateUI();
        }

        /**
         * RENDEREZAÇÃO NO CANVAS (DESENHO DOS NÓS, RÁDIO E CONEXÕES)
         */
        function renderCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            const radioRange = parseFloat(document.getElementById('paramRadioRange').value);

            // 1. Desenhar Área de Cobertura e Ondas de Rádio
            state.nodes.forEach(node => {
                if (!node.isActive) return;

                // Círculo de alcance de rádio
                ctx.beginPath();
                ctx.arc(node.x, node.y, radioRange, 0, Math.PI * 2);
                ctx.fillStyle = node.isGateway ? 'rgba(16, 185, 129, 0.03)' : 'rgba(99, 102, 241, 0.03)';
                ctx.fill();
                ctx.strokeStyle = node.isGateway ? 'rgba(16, 185, 129, 0.2)' : 'rgba(99, 102, 241, 0.15)';
                ctx.setLineDash([4, 4]);
                ctx.stroke();
                ctx.setLineDash([]);

                // Pulso do rádio em animação
                if (state.isRunning) {
                    ctx.beginPath();
                    ctx.arc(node.x, node.y, node.pulseRadius, 0, Math.PI * 2);
                    ctx.strokeStyle = node.isGateway ? 'rgba(16, 185, 129, 0.15)' : 'rgba(99, 102, 241, 0.15)';
                    ctx.stroke();
                }
            });

            // 2. Desenhar Ícones e Status dos Nós
            state.nodes.forEach(node => {
                ctx.save();
                ctx.translate(node.x, node.y);

                // Sombra
                ctx.shadowColor = node.isGateway ? 'rgba(16, 185, 129, 0.4)' : 'rgba(99, 102, 241, 0.4)';
                ctx.shadowBlur = 12;

                // Corpo do Nó
                ctx.beginPath();
                ctx.arc(0, 0, node.isGateway ? 22 : 18, 0, Math.PI * 2);
                
                if (!node.isActive) {
                    ctx.fillStyle = '#475569'; // Desativado
                } else if (node.isGateway) {
                    ctx.fillStyle = '#10b981'; // Gateway Emerald
                } else {
                    ctx.fillStyle = '#6366f1'; // Móvel Indigo
                }
                ctx.fill();

                ctx.strokeStyle = '#0f172a';
                ctx.lineWidth = 3;
                ctx.stroke();
                ctx.restore();

                // Indicador de Carga/Mensagens no Buffer (Badges em volta)
                if (node.buffer.length > 0 && node.isActive) {
                    ctx.beginPath();
                    ctx.arc(node.x + 14, node.y - 14, 10, 0, Math.PI * 2);
                    ctx.fillStyle = '#f59e0b'; // Amber badge
                    ctx.fill();
                    ctx.fillStyle = '#000000';
                    ctx.font = 'bold 10px Inter, sans-serif';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(node.buffer.length, node.x + 14, node.y - 14);
                }

                // Nome/ID do Nó
                ctx.fillStyle = node.isActive ? '#f8fafc' : '#94a3b8';
                ctx.font = 'bold 12px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.fillText(node.id, node.x, node.y + 35);

                // Subtítulo da conexão
                ctx.font = '10px Inter, sans-serif';
                ctx.fillStyle = node.hasInternet ? '#34d399' : '#64748b';
                ctx.fillText(node.hasInternet ? 'Internet' : 'DTN Offline', node.x, node.y + 48);
            });
        }

        // Linhas de Contato Energéticas entre nós em alcance
        function drawContactLine(nodeA, nodeB) {
            ctx.save();
            ctx.beginPath();
            ctx.moveTo(nodeA.x, nodeA.y);
            ctx.lineTo(nodeB.x, nodeB.y);
            ctx.strokeStyle = 'rgba(56, 189, 248, 0.5)'; // Light cyan contact
            ctx.lineWidth = 2;
            ctx.setLineDash([6, 3]);
            ctx.stroke();
            ctx.restore();
        }

        /**
         * INTERATIVIDADE COM MOUSE (ARRASTAR NÓS NO CANVAS)
         */
        canvas.addEventListener('mousedown', e => {
            const rect = canvas.getBoundingClientRect();
            const mouseX = e.clientX - rect.left;
            const mouseY = e.clientY - rect.top;

            // Verificar clique num nó
            state.nodes.forEach(node => {
                const dist = Math.hypot(node.x - mouseX, node.y - mouseY);
                if (dist <= 25) {
                    state.selectedNodeForDrag = node;
                }
            });
        });

        canvas.addEventListener('mousemove', e => {
            if (state.selectedNodeForDrag) {
                const rect = canvas.getBoundingClientRect();
                state.selectedNodeForDrag.x = e.clientX - rect.left;
                state.selectedNodeForDrag.y = e.clientY - rect.top;
                state.selectedNodeForDrag.targetX = state.selectedNodeForDrag.x;
                state.selectedNodeForDrag.targetY = state.selectedNodeForDrag.y;
                renderCanvas();
            }
        });

        window.addEventListener('mouseup', () => {
            state.selectedNodeForDrag = null;
        });

        /**
         * SISTEMA DE LOGS E CONSOLE
         */
        function logEvent(msg, type = 'info') {
            const timeStr = `[${state.simTime.toFixed(1)}s]`;
            const logEntry = document.createElement('div');
            logEntry.className = 'text-xs leading-relaxed border-b border-slate-900/60 pb-0.5';

            let typeStyle = 'text-slate-300';
            if (type === 'success') typeStyle = 'text-emerald-400 bg-emerald-950/20 px-1 rounded';
            if (type === 'transfer') typeStyle = 'text-indigo-300';
            if (type === 'warning') typeStyle = 'text-amber-400';
            if (type === 'error') typeStyle = 'text-rose-400';
            if (type === 'create') typeStyle = 'text-cyan-300';

            logEntry.innerHTML = `<span class="text-slate-500 font-mono">${timeStr}</span> <span class="${typeStyle}">${msg}</span>`;

            const consoleContainer = document.getElementById('logConsole');
            consoleContainer.appendChild(logEntry);
            consoleContainer.scrollTop = consoleContainer.scrollHeight;
        }

        /**
         * ATUALIZAÇÃO DA INTERFACE & PAINÉIS LATERAIS
         */
        function updateUI() {
            // Preencher Selects de Origem/Destino
            const selectOrigin = document.getElementById('msgOrigin');
            const selectDest = document.getElementById('msgDestination');

            const currentOrigin = selectOrigin.value;
            const currentDest = selectDest.value;

            selectOrigin.innerHTML = '';
            selectDest.innerHTML = '';

            state.nodes.forEach(node => {
                if (!node.isGateway) {
                    const opt1 = document.createElement('option');
                    opt1.value = node.id;
                    opt1.textContent = node.id;
                    selectOrigin.appendChild(opt1);
                }

                const opt2 = document.createElement('option');
                opt2.value = node.id;
                opt2.textContent = node.id + (node.isGateway ? ' (Gateway)' : '');
                selectDest.appendChild(opt2);
            });

            if (currentOrigin) selectOrigin.value = currentOrigin;
            if (currentDest) selectDest.value = currentDest;

            // Renderizar Lista de Gestão de Nós
            const nodesList = document.getElementById('nodesList');
            nodesList.innerHTML = '';

            state.nodes.forEach(node => {
                const item = document.createElement('div');
                item.className = 'bg-slate-800/60 border border-slate-700/60 rounded-lg p-2 flex items-center justify-between text-xs';

                item.innerHTML = `
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full ${node.isActive ? (node.isGateway ? 'bg-emerald-500' : 'bg-indigo-500') : 'bg-slate-600'}"></span>
                        <span class="font-bold text-slate-200">${node.id}</span>
                        <span class="text-slate-500">(${node.buffer.length} msgs)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button onclick="toggleNodePower('${node.id}')" title="Ligar/Desligar Bateria" class="px-2 py-0.5 rounded text-[10px] ${node.isActive ? 'bg-slate-700 text-slate-300 hover:bg-slate-600' : 'bg-rose-950 text-rose-300'}">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                        <button onclick="inspectNodeBuffer('${node.id}')" title="Inspecionar Buffer" class="px-2 py-0.5 rounded text-[10px] bg-indigo-900/50 text-indigo-300 hover:bg-indigo-800/60">
                            <i class="fa-solid fa-search"></i>
                        </button>
                    </div>
                `;
                nodesList.appendChild(item);
            });
        }

        /**
         * MÉTRICAS E GRÁFICOS EM TEMPO REAL
         */
        function updateMetrics() {
            // Rate = Entregues / Criadas
            const deliveryRate = state.stats.created > 0 ? ((state.stats.delivered / state.stats.created) * 100).toFixed(1) : '0.0';
            document.getElementById('metricDeliveryRate').textContent = `${deliveryRate}%`;
            document.getElementById('metricCreatedDelivered').textContent = `${state.stats.created} / ${state.stats.delivered}`;

            // Overhead = Replicações / Entregues
            const overhead = state.stats.delivered > 0 ? (state.stats.replications / state.stats.delivered).toFixed(1) : '0.0';
            document.getElementById('metricOverhead').textContent = `${overhead}x`;

            // Latência Média
            const avgLatency = state.stats.delivered > 0 ? (state.stats.totalLatency / state.stats.delivered).toFixed(1) : '0.0';
            document.getElementById('metricAvgLatency').textContent = `${avgLatency}s`;

            // Atualizar Gráficos Chart.js
            updateCharts();
        }

        /**
         * INICIALIZAÇÃO DOS GRÁFICOS (CHART.JS)
         */
        function initCharts() {
            // Chart 1: Performance Timeline
            const ctxPerf = document.getElementById('chartPerformance').getContext('2d');
            if (chartPerformance) chartPerformance.destroy();

            chartPerformance = new Chart(ctxPerf, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        {
                            label: 'Entregues',
                            data: [],
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Cópias / Cópia Epidêmica',
                            data: [],
                            borderColor: '#f59e0b',
                            borderDash: [5, 5],
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: '#94a3b8', font: { size: 10 } } } },
                    scales: {
                        x: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: '#1e293b' } },
                        y: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: '#1e293b' }, beginAtZero: true }
                    }
                }
            });

            // Chart 2: Buffer Utilization
            const ctxBuf = document.getElementById('chartBuffers').getContext('2d');
            if (chartBuffers) chartBuffers.destroy();

            chartBuffers = new Chart(ctxBuf, {
                type: 'bar',
                data: {
                    labels: state.nodes.map(n => n.id),
                    datasets: [{
                        label: 'Ocupação do Buffer (Bundles)',
                        data: state.nodes.map(n => n.buffer.length),
                        backgroundColor: state.nodes.map(n => n.isGateway ? '#10b981' : '#6366f1'),
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#94a3b8', font: { size: 10 } }, grid: { display: false } },
                        y: { ticks: { color: '#64748b', font: { size: 9 } }, grid: { color: '#1e293b' }, beginAtZero: true, stepSize: 1 }
                    }
                }
            });
        }

        let lastChartUpdate = 0;
        function updateCharts() {
            if (state.simTime - lastChartUpdate > 2) { // Atualiza a cada 2s de simulação
                lastChartUpdate = state.simTime;

                // Dataset 1
                if (chartPerformance) {
                    chartPerformance.data.labels.push(`${state.simTime.toFixed(0)}s`);
                    chartPerformance.data.datasets[0].data.push(state.stats.delivered);
                    chartPerformance.data.datasets[1].data.push(state.stats.replications);

                    if (chartPerformance.data.labels.length > 15) {
                        chartPerformance.data.labels.shift();
                        chartPerformance.data.datasets[0].data.shift();
                        chartPerformance.data.datasets[1].data.shift();
                    }
                    chartPerformance.update('none');
                }

                // Dataset 2
                if (chartBuffers) {
                    chartBuffers.data.labels = state.nodes.map(n => n.id);
                    chartBuffers.data.datasets[0].data = state.nodes.map(n => n.buffer.length);
                    chartBuffers.update('none');
                }
            }
        }

        /**
         * FUNÇÕES AUXILIARES DE GESTÃO DE NÓS
         */
        window.toggleNodePower = function(nodeId) {
            const node = state.nodes.find(n => n.id === nodeId);
            if (node) {
                node.isActive = !node.isActive;
                logEvent(`Status do <b>${node.id}</b> alterado para: ${node.isActive ? 'ATIVO' : 'DESLIGADO/SEM BATERIA'}.`, node.isActive ? 'info' : 'warning');
                updateUI();
                renderCanvas();
            }
        };

        window.inspectNodeBuffer = function(nodeId) {
            const node = state.nodes.find(n => n.id === nodeId);
            if (!node || node.buffer.length === 0) {
                alert(`O buffer do ${nodeId} está vazio!`);
                return;
            }

            const bundleId = node.buffer[0];
            const bundle = state.bundles.find(b => b.id === bundleId);
            if (bundle) openBundleModal(bundle);
        };

        function openBundleModal(bundle) {
            document.getElementById('modalBundleId').textContent = `ID: ${bundle.id}`;
            document.getElementById('modalBundleContent').textContent = bundle.content;
            document.getElementById('modalBundleOrigin').textContent = bundle.originId;
            document.getElementById('modalBundleDest').textContent = bundle.destId;
            document.getElementById('modalBundleStatus').textContent = bundle.delivered ? 'Entregue no Gateway' : 'Em Trânsito / Armazenado';
            document.getElementById('modalBundleStatus').className = bundle.delivered ? 'font-bold text-emerald-400' : 'font-bold text-amber-400';

            // Contar réplicas ativas na rede
            let copyCount = 0;
            state.nodes.forEach(n => { if (n.buffer.includes(bundle.id)) copyCount++; });
            document.getElementById('modalBundleCopies').textContent = copyCount;

            // Renderizar Hops
            const hopsContainer = document.getElementById('modalBundleHops');
            hopsContainer.innerHTML = '';
            bundle.hops.forEach((hop, idx) => {
                const item = document.createElement('div');
                item.className = 'text-xs text-slate-300';
                item.innerHTML = `<span class="text-slate-500">Hop ${idx + 1}:</span> [${hop.time.toFixed(1)}s] Replicado para <b>${hop.nodeId}</b>`;
                hopsContainer.appendChild(item);
            });

            document.getElementById('bundleModal').classList.remove('hidden');
        }

        /**
         * EVENT LISTENERS E CONTROLES DE INTERFACE
         */
        document.getElementById('btnPlayPause').addEventListener('click', () => {
            state.isRunning = !state.isRunning;
            const btnText = document.getElementById('textPlay');
            const btnIcon = document.getElementById('iconPlay');

            if (state.isRunning) {
                btnText.textContent = 'Pausar';
                btnIcon.className = 'fa-solid fa-pause';
                logEvent('Simulação iniciada.');
            } else {
                btnText.textContent = 'Iniciar';
                btnIcon.className = 'fa-solid fa-play';
                logEvent('Simulação pausada.');
            }
        });

        document.getElementById('btnStep').addEventListener('click', () => {
            state.isRunning = false;
            document.getElementById('textPlay').textContent = 'Iniciar';
            document.getElementById('iconPlay').className = 'fa-solid fa-play';

            state.simTime += 0.5;
            const autoMobility = document.getElementById('paramAutoMobility').checked;
            state.nodes.forEach(node => node.update(0.5, canvas.width, canvas.height, autoMobility));
            processDTNContacts();
            processTTLAndPurging();
            updateMetrics();
            renderCanvas();
            logEvent(`Passo de simulação avançado (+0.5s). Tempo: ${state.simTime.toFixed(1)}s`);
        });

        document.getElementById('btnReset').addEventListener('click', () => {
            state.isRunning = false;
            document.getElementById('textPlay').textContent = 'Iniciar';
            document.getElementById('iconPlay').className = 'fa-solid fa-play';
            document.getElementById('logConsole').innerHTML = '';
            initSimulation();
        });

        document.getElementById('simSpeed').addEventListener('change', (e) => {
            state.speedMultiplier = parseFloat(e.target.value);
        });

        document.getElementById('btnCreateMsg').addEventListener('click', createBundle);

        document.getElementById('btnAddNode').addEventListener('click', () => {
            const charCode = 65 + state.nodes.filter(n => !n.isGateway).length;
            const nodeName = `Nó ${String.fromCharCode(charCode)}`;
            const newNode = new Node(nodeName, 100 + Math.random() * (canvas.width - 200), 100 + Math.random() * (canvas.height - 200));
            state.nodes.push(newNode);
            updateUI();
            renderCanvas();
            logEvent(`Novo nó <b>${nodeName}</b> adicionado à rede.`, 'info');
        });

        document.getElementById('btnClearLogs').addEventListener('click', () => {
            document.getElementById('logConsole').innerHTML = '';
        });

        document.getElementById('btnCloseModal').addEventListener('click', () => {
            document.getElementById('bundleModal').classList.add('hidden');
        });

        // Inicialização Global na Carga da Janela
        window.onload = function() {
            initSimulation();
            requestAnimationFrame(simulationLoop);
        };

    </script>
</body>

</html>
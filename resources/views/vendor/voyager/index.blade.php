@extends('voyager::master')

@section('page_header')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h2>Hola, {{ Auth::user()->name }}</h2>
                                <p class="text-muted">Resumen de rendimiento - {{ now()->format('d F Y') }}</p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary" id="refresh-dashboard">
                                        <i class="voyager-refresh"></i> Actualizar
                                    </button>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
    @php
        $meses = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');       
    @endphp
    
    <div class="page-content container-fluid">
        @include('voyager::alerts')
        @include('voyager::dimmers')

        <!-- KPI Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel panel-bordered dashboard-kpi">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="voyager-dollar"></i>
                        </div>
                        <h3 class="kpi-value">$24,580</h3>
                        <p class="kpi-label">Ventas Totales</p>
                        <div class="kpi-trend trend-up">
                            <i class="voyager-up"></i> 12.5%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered dashboard-kpi">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="voyager-bag"></i>
                        </div>
                        <h3 class="kpi-value">328</h3>
                        <p class="kpi-label">Pedidos Hoy</p>
                        <div class="kpi-trend trend-up">
                            <i class="voyager-up"></i> 5.2%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered dashboard-kpi">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="voyager-person"></i>
                        </div>
                        <h3 class="kpi-value">42</h3>
                        <p class="kpi-label">Nuevos Clientes</p>
                        <div class="kpi-trend trend-down">
                            <i class="voyager-down"></i> 3.1%
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel panel-bordered dashboard-kpi">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="voyager-bar-chart"></i>
                        </div>
                        <h3 class="kpi-value">$78.50</h3>
                        <p class="kpi-label">Ticket Promedio</p>
                        <div class="kpi-trend trend-up">
                            <i class="voyager-up"></i> 8.7%
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de ventas mensuales con controles -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <div class="panel-title-container">
                            <h3 class="panel-title">Ventas Mensuales</h3>
                            <div class="chart-controls">
                                <select class="form-control chart-type-selector" data-chart="ventasMensualesChart">
                                    <option value="bar">Barras</option>
                                    <option value="line">Líneas</option>
                                </select>
                                <button class="btn btn-sm btn-default chart-export" data-chart="ventasMensualesChart">
                                    <i class="voyager-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="ventasMensualesChart" height="250"></canvas>
                        </div>
                        <div class="chart-summary">
                            <div class="summary-item">
                                <span class="summary-label">Total Mes:</span>
                                <span class="summary-value">$2,580,000</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Crecimiento:</span>
                                <span class="summary-value trend-up">+12.5%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de productos más vendidos con filtros -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <div class="panel-title-container">
                            <h3 class="panel-title">Productos Más Vendidos</h3>
                            <div class="chart-controls">
                                <select class="form-control chart-period-selector" data-chart="topProductosChart">
                                    <option value="week">Esta semana</option>
                                    <option value="month" selected>Este mes</option>
                                    <option value="year">Este año</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="topProductosChart" height="250"></canvas>
                        </div>
                        <div class="chart-legend-detailed">
                            <div class="legend-item">
                                <span class="legend-color" style="background-color: rgba(255, 99, 132, 0.7)"></span>
                                <span class="legend-label">Hamburguesa</span>
                                <span class="legend-value">1,200 unidades</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de ventas por día de la semana interactivo -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <div class="panel-title-container">
                            <h3 class="panel-title">Ventas por Día de la Semana</h3>
                            <div class="chart-controls">
                                <button class="btn btn-sm btn-default toggle-dataset" data-chart="ventasDiasChart">
                                    <i class="voyager-eye"></i> Alternar Datos
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="ventasDiasChart" height="250"></canvas>
                        </div>
                        <div class="chart-tooltip-info">
                            <i class="voyager-info"></i> Haz clic en los elementos para ver detalles
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de comparación anual mejorado -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <div class="panel-title-container">
                            <h3 class="panel-title">Comparación Anual</h3>
                            <div class="chart-controls">
                                <select class="form-control year-selector" data-chart="comparacionAnualChart">
                                    <option value="2021">2021-2022</option>
                                    <option value="2022" selected>2022-2023</option>
                                    <option value="2023">2023-2024</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="comparacionAnualChart" height="250"></canvas>
                        </div>
                        <div class="comparison-stats">
                            <div class="stat-item">
                                <span class="stat-year">2022</span>
                                <span class="stat-total">$2,340,000</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-year">2023</span>
                                <span class="stat-total">$2,580,000</span>
                                <span class="stat-difference trend-up">+$240,000</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Nuevos gráficos agregados -->
        <div class="row">
            <!-- Gráfico de ventas en tiempo real -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <div class="panel-title-container">
                            <h3 class="panel-title">Ventas en Tiempo Real - Hoy</h3>
                            <div class="chart-controls">
                                <button class="btn btn-sm btn-success" id="start-realtime">
                                    <i class="voyager-play"></i> Iniciar
                                </button>
                                <button class="btn btn-sm btn-danger" id="stop-realtime">
                                    <i class="voyager-pause"></i> Pausar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="ventasTiempoRealChart" height="200"></canvas>
                        </div>
                        <div class="realtime-info">
                            <span class="realtime-label">Actualizado:</span>
                            <span class="realtime-time" id="lastUpdateTime">{{ now()->format('H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de métricas de rendimiento -->
            <div class="col-md-6">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Métricas de Rendimiento</h3>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="metricasRendimientoChart" height="200"></canvas>
                        </div>
                        <div class="metrics-radar-info">
                            <div class="metric-score">
                                <span class="score-value">85%</span>
                                <span class="score-label">Puntuación General</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráfico de embudo de conversión -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Embudo de Conversión</h3>
                    </div>
                    <div class="panel-body">
                        <div class="chart-container">
                            <canvas id="embudoConversionChart" height="150"></canvas>
                        </div>
                        <div class="funnel-stats">
                            <div class="funnel-stage">
                                <span class="stage-name">Visitantes</span>
                                <span class="stage-value">10,000</span>
                                <span class="stage-rate">100%</span>
                            </div>
                            <div class="funnel-stage">
                                <span class="stage-name">Carrito</span>
                                <span class="stage-value">2,500</span>
                                <span class="stage-rate">25%</span>
                            </div>
                            <div class="funnel-stage">
                                <span class="stage-name">Checkout</span>
                                <span class="stage-value">1,200</span>
                                <span class="stage-rate">12%</span>
                            </div>
                            <div class="funnel-stage">
                                <span class="stage-name">Completado</span>
                                <span class="stage-value">980</span>
                                <span class="stage-rate">9.8%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de últimos pedidos -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-heading">
                        <h3 class="panel-title">Pedidos Recientes</h3>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th># Pedido</th>
                                        <th>Cliente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#12345</td>
                                        <td>Juan Pérez</td>
                                        <td>20 Nov 2023</td>
                                        <td>$125.80</td>
                                        <td><span class="label label-success">Completado</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#12344</td>
                                        <td>María García</td>
                                        <td>20 Nov 2023</td>
                                        <td>$89.50</td>
                                        <td><span class="label label-warning">Procesando</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#12343</td>
                                        <td>Carlos López</td>
                                        <td>19 Nov 2023</td>
                                        <td>$210.00</td>
                                        <td><span class="label label-success">Completado</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#12342</td>
                                        <td>Ana Martínez</td>
                                        <td>19 Nov 2023</td>
                                        <td>$56.90</td>
                                        <td><span class="label label-danger">Cancelado</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>#12341</td>
                                        <td>Pedro Sánchez</td>
                                        <td>18 Nov 2023</td>
                                        <td>$178.30</td>
                                        <td><span class="label label-success">Completado</span></td>
                                        <td>
                                            <a href="#" class="btn btn-sm btn-primary">Ver</a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .dashboard-kpi {
            transition: all 0.3s ease;
        }
        .dashboard-kpi:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .kpi-icon {
            font-size: 24px;
            color: #22A7F0;
            margin-bottom: 10px;
        }
        .kpi-value {
            font-size: 28px;
            font-weight: bold;
            margin: 10px 0;
        }
        .kpi-label {
            color: #6c757d;
            margin-bottom: 5px;
        }
        .kpi-trend {
            font-size: 12px;
            font-weight: bold;
        }
        .trend-up {
            color: #2ecc71;
        }
        .trend-down {
            color: #e74c3c;
        }
        .panel-heading .btn-group {
            margin-top: -5px;
        }
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        /* Nuevos estilos para funcionalidades adicionales */
        .panel-title-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .chart-controls {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .chart-controls select {
            width: auto;
            display: inline-block;
        }
        .chart-summary {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            display: block;
            font-size: 12px;
            color: #6c757d;
        }
        .summary-value {
            display: block;
            font-weight: bold;
            font-size: 16px;
        }
        .chart-legend-detailed {
            margin-top: 15px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            padding: 5px;
            border-radius: 3px;
            background: #f8f9fa;
        }
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 3px;
            margin-right: 10px;
        }
        .legend-label {
            flex: 1;
            font-size: 12px;
        }
        .legend-value {
            font-weight: bold;
            font-size: 12px;
        }
        .chart-tooltip-info {
            text-align: center;
            font-size: 11px;
            color: #6c757d;
            margin-top: 10px;
        }
        .comparison-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        .stat-item {
            text-align: center;
        }
        .stat-year {
            display: block;
            font-weight: bold;
        }
        .stat-total {
            display: block;
            font-size: 14px;
            color: #333;
        }
        .stat-difference {
            display: block;
            font-size: 12px;
        }
        .realtime-info {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }
        .realtime-time {
            font-weight: bold;
            color: #22A7F0;
        }
        .metrics-radar-info {
            text-align: center;
            margin-top: 15px;
        }
        .metric-score {
            display: inline-block;
            text-align: center;
        }
        .score-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #22A7F0;
        }
        .score-label {
            font-size: 12px;
            color: #6c757d;
        }
        .funnel-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        .funnel-stage {
            text-align: center;
            flex: 1;
        }
        .stage-name {
            display: block;
            font-weight: bold;
            font-size: 12px;
        }
        .stage-value {
            display: block;
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .stage-rate {
            display: block;
            font-size: 12px;
            color: #6c757d;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .panel-title-container {
                flex-direction: column;
                align-items: flex-start;
            }
            .chart-controls {
                margin-top: 10px;
                width: 100%;
                justify-content: flex-end;
            }
            .chart-summary,
            .comparison-stats,
            .funnel-stats {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
@stop

@section('javascript')
    <!-- Incluir Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        $(document).ready(function(){   
            // Registrar plugins
            Chart.register(ChartDataLabels);
            
            // Variables para control de tiempo real
            let realtimeInterval;
            let realtimeChart;
            
            // Datos de ejemplo mejorados
            const ventasMensualesData = {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [{
                    label: 'Ventas 2023',
                    data: [120000, 190000, 150000, 180000, 210000, 230000, 250000, 220000, 240000, 260000, 280000, 300000],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2
                }]
            };

            const topProductosData = {
                labels: ['Hamburguesa', 'Pizza', 'Ensalada', 'Bebida', 'Postre'],
                datasets: [{
                    label: 'Unidades Vendidas',
                    data: [1200, 800, 500, 1500, 300],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            };

            const ventasDiasData = {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'],
                datasets: [{
                    label: 'Ventas promedio',
                    data: [80000, 85000, 90000, 95000, 120000, 150000, 130000],
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            };

            const comparacionAnualData = {
                labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                datasets: [
                    {
                        label: '2022',
                        data: [100000, 150000, 130000, 160000, 190000, 210000, 230000, 200000, 220000, 240000, 260000, 280000],
                        borderColor: 'rgba(201, 203, 207, 1)',
                        backgroundColor: 'rgba(201, 203, 207, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    },
                    {
                        label: '2023',
                        data: [120000, 190000, 150000, 180000, 210000, 230000, 250000, 220000, 240000, 260000, 280000, 300000],
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }
                ]
            };

            // Nuevos datos para gráficos adicionales
            const ventasTiempoRealData = {
                labels: [],
                datasets: [{
                    label: 'Ventas por Hora',
                    data: [],
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            };

            const metricasRendimientoData = {
                labels: ['Ventas', 'Clientes', 'Eficiencia', 'Calidad', 'Rentabilidad', 'Crecimiento'],
                datasets: [{
                    label: 'Rendimiento Actual',
                    data: [85, 78, 92, 88, 76, 90],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    pointBorderColor: '#fff',
                    pointHoverBackgroundColor: '#fff',
                    pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
                }]
            };

            const embudoConversionData = {
                labels: ['Visitantes', 'Carrito', 'Checkout', 'Completado'],
                datasets: [{
                    data: [10000, 2500, 1200, 980],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(255, 159, 64, 0.7)',
                        'rgba(255, 205, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)'
                    ],
                    borderWidth: 1
                }]
            };

            // Configuración común para los gráficos
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== undefined) {
                                    label += new Intl.NumberFormat('en-US', {
                                        style: 'currency',
                                        currency: 'USD'
                                    }).format(context.parsed.y);
                                } else {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false
                        },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) {
                                    return '$' + value / 1000 + 'k';
                                }
                                return '$' + value;
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            };
            
            const pieChartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    datalabels: {
                        color: '#fff',
                        font: {
                            weight: 'bold'
                        },
                        formatter: (value, ctx) => {
                            return ctx.chart.data.labels[ctx.dataIndex];
                        },
                    }
                }
            };

            const realtimeOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                animation: {
                    duration: 0
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };

            const radarOptions = {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: {
                            display: true
                        },
                        suggestedMin: 0,
                        suggestedMax: 100
                    }
                }
            };

            const funnelOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed} visitas`;
                            }
                        }
                    }
                }
            };

            // Crear los gráficos principales
            const ventasMensualesChart = new Chart(document.getElementById('ventasMensualesChart'), {
                type: 'bar',
                data: ventasMensualesData,
                options: chartOptions
            });

            const topProductosChart = new Chart(document.getElementById('topProductosChart'), {
                type: 'pie',
                data: topProductosData,
                options: pieChartOptions
            });

            const ventasDiasChart = new Chart(document.getElementById('ventasDiasChart'), {
                type: 'line',
                data: ventasDiasData,
                options: chartOptions
            });

            const comparacionAnualChart = new Chart(document.getElementById('comparacionAnualChart'), {
                type: 'line',
                data: comparacionAnualData,
                options: chartOptions
            });

            // Crear nuevos gráficos
            realtimeChart = new Chart(document.getElementById('ventasTiempoRealChart'), {
                type: 'line',
                data: ventasTiempoRealData,
                options: realtimeOptions
            });

            new Chart(document.getElementById('metricasRendimientoChart'), {
                type: 'radar',
                data: metricasRendimientoData,
                options: radarOptions
            });

            new Chart(document.getElementById('embudoConversionChart'), {
                type: 'bar',
                data: embudoConversionData,
                options: funnelOptions
            });

            // Funcionalidades adicionales

            // Cambiar tipo de gráfico
            $('.chart-type-selector').change(function() {
                const chartId = $(this).data('chart');
                const newType = $(this).val();
                
                if (chartId === 'ventasMensualesChart') {
                    ventasMensualesChart.config.type = newType;
                    ventasMensualesChart.update();
                }
            });

            // Exportar gráfico
            $('.chart-export').click(function() {
                const chartId = $(this).data('chart');
                const canvas = document.getElementById(chartId);
                const url = canvas.toDataURL('image/png');
                
                const link = document.createElement('a');
                link.download = `${chartId}.png`;
                link.href = url;
                link.click();
            });

            // Alternar dataset
            $('.toggle-dataset').click(function() {
                const chart = ventasDiasChart;
                const currentData = chart.data.datasets[0].data;
                
                // Datos alternativos
                const alternativeData = [70000, 78000, 82000, 88000, 110000, 140000, 125000];
                
                if (JSON.stringify(currentData) === JSON.stringify(ventasDiasData.datasets[0].data)) {
                    chart.data.datasets[0].data = alternativeData;
                    chart.data.datasets[0].label = 'Ventas proyectadas';
                } else {
                    chart.data.datasets[0].data = ventasDiasData.datasets[0].data;
                    chart.data.datasets[0].label = 'Ventas promedio';
                }
                
                chart.update();
            });

            // Tiempo real
            $('#start-realtime').click(function() {
                if (realtimeInterval) {
                    clearInterval(realtimeInterval);
                }
                
                realtimeInterval = setInterval(() => {
                    const now = new Date();
                    const timeLabel = now.getHours() + ':' + now.getMinutes() + ':' + now.getSeconds();
                    
                    // Agregar nuevo dato aleatorio
                    const newData = Math.floor(Math.random() * 1000) + 500;
                    
                    realtimeChart.data.labels.push(timeLabel);
                    realtimeChart.data.datasets[0].data.push(newData);
                    
                    // Mantener solo últimos 10 puntos
                    if (realtimeChart.data.labels.length > 10) {
                        realtimeChart.data.labels.shift();
                        realtimeChart.data.datasets[0].data.shift();
                    }
                    
                    realtimeChart.update('none');
                    
                    // Actualizar timestamp
                    $('#lastUpdateTime').text(timeLabel);
                }, 2000);
            });

            $('#stop-realtime').click(function() {
                if (realtimeInterval) {
                    clearInterval(realtimeInterval);
                    realtimeInterval = null;
                }
            });

            // Selector de año para comparación
            $('.year-selector').change(function() {
                const selectedYear = $(this).val();
                // En una implementación real, aquí harías una petición AJAX
                // para obtener los datos del año seleccionado
                alert(`Cargando datos para años ${selectedYear}-${parseInt(selectedYear)+1}`);
            });

            // Selector de período para productos
            $('.chart-period-selector').change(function() {
                const period = $(this).val();
                // En una implementación real, aquí harías una petición AJAX
                alert(`Cargando productos más vendidos del ${period}`);
            });

            // Interactividad en gráficos
            ventasDiasChart.canvas.addEventListener('click', function(evt) {
                const points = ventasDiasChart.getElementsAtEventForMode(evt, 'nearest', { intersect: true }, true);
                if (points.length) {
                    const firstPoint = points[0];
                    const label = ventasDiasChart.data.labels[firstPoint.index];
                    const value = ventasDiasChart.data.datasets[firstPoint.datasetIndex].data[firstPoint.index];
                    alert(`Ventas del ${label}: $${value.toLocaleString()}`);
                }
            });

            // Botón de refresh
            $('#refresh-dashboard').click(function() {
                const $btn = $(this);
                const originalText = $btn.html();
                
                $btn.prop('disabled', true).html('<i class="voyager-refresh"></i> Actualizando...');
                
                // Simular actualización de datos
                setTimeout(() => {
                    // En una implementación real, aquí actualizarías los datos
                    ventasMensualesChart.update();
                    topProductosChart.update();
                    ventasDiasChart.update();
                    comparacionAnualChart.update();
                    
                    $btn.prop('disabled', false).html(originalText);
                    showToast('Datos actualizados correctamente', 'success');
                }, 1500);
            });

            // Función para mostrar notificaciones
            function showToast(message, type = 'info') {
                // Implementación básica de toast
                const toast = $(`
                    <div class="alert alert-${type} alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        ${message}
                    </div>
                `);
                
                $('body').append(toast);
                
                setTimeout(() => {
                    toast.alert('close');
                }, 3000);
            }

            // Selector de rango de tiempo
            $('.dropdown-menu a[data-range]').click(function(e) {
                e.preventDefault();
                const range = $(this).data('range');
                // En una implementación real, aquí actualizarías todos los gráficos
                // según el rango de tiempo seleccionado
                alert(`Filtrando datos para: ${range}`);
            });
        });
    </script>
@stop
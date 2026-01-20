<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CampusFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-color: #1e3d59;
            --secondary-color: #2a4d6e;
            --light-blue: #e6f0f9;
            --dark-gray: #424242;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #fafafa;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(145deg, var(--primary-color), var(--secondary-color));
            width: 280px;
            position: fixed;
            top: 0;
            bottom: 0;
            padding: 1.5rem 1rem;
            color: white;
            transition: all 0.3s;
            z-index: 1001;
            overflow-y: auto;
        }

        .admin-profile {
            text-align: center;
            margin-bottom: 2rem;
        }

        .admin-profile img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 1rem;
        }

        .sidebar .nav {
            margin-top: 2rem;
            padding: 0;
        }

        .nav-item {
            width: 100%;
            margin-bottom: 0.5rem;
            list-style: none;
            position: relative;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.8rem 1.2rem;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            text-decoration: none;
            font-weight: 500;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar .nav-link i {
            margin-right: 12px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
        }

        .dropdown-menu {
            padding: 0.5rem 0;
            margin: 0;
            display: none;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            list-style: none;
            position: static;
            width: 100%;
            border-radius: 10px;
            margin-left: 2rem;
        }

        .dropdown-menu.show {
            display: block;
        }

        .dropdown-menu li {
            margin-bottom: 0.5rem;
        }

        .dropdown-menu a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            padding: 0.5rem 1rem;
            display: block;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .dropdown-menu a:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .main-content {
            margin-left: 280px;
            margin-top: 20px;
            padding: 2rem;
            flex-grow: 1;
            transition: all 0.3s;
        }

        .dashboard-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .dashboard-card:hover {
            transform: translateY(-5px);
        }

        .stat-card {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .sidebar-toggle {
            position: fixed;
            left: 1rem;
            top: 1rem;
            z-index: 1001;
            display: none;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }

            .sidebar.active {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0 !important;
            }

            .sidebar-toggle {
                display: block;
            }
        }
    </style>
</head>

<body>
    <!-- Sidebar Toggle Button -->
    <button class="btn btn-primary sidebar-toggle">
        <i class="bi bi-list"></i>
    </button>

    <!-- Sidebar -->
    <nav class="sidebar">
        <div class="admin-profile">
            <img src="images_pages/logo_enseignant.png" alt="Admin" class="admin-avatar">
            <h5 class="mb-2">Administrateur</h5>
            <p class="text-light mb-0">Campus Manager</p>
        </div>

        <ul class="nav flex-column mt-4">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="bi bi-house-door"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-mortarboard"></i> Étudiants
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EtudiantListe.php">Liste des Étudiants</a></li>
                    <li><a href="ajouter_etudiant.php">Ajouter un étudiant</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-person-workspace"></i> Professeurs
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ProfesseurListe.php">Liste des Professeurs</a></li>
                    <li><a href="ajouter_professeur.php">Ajouter un Professeur</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-book"></i> Matières
                </a>
                <ul class="dropdown-menu">
                    <li><a href="ListeMatiere.php">Liste des Matières</a></li>
                    <li><a href="ajouter_matiere.php">Ajouter une Attribution</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-cash-coin"></i> Finances
                </a>
                <ul class="dropdown-menu">
                    <li><a href="VersementListe.php">Liste des versements</a></li>
                    <li><a href="versement.php">Effectuer un versement</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-card-checklist"></i> Notes
                </a>
                <ul class="dropdown-menu">
                    <li><a href="noteb1.php">Notes B1</a></li>
                    <li><a href="noteb2.php">Notes B2</a></li>
                    <li><a href="noteb3.php">Notes B3</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#">
                    <i class="bi bi-calendar-week"></i> Emploi du temps
                </a>
                <ul class="dropdown-menu">
                    <li><a href="EmploiDuTempsB1.php">Emploi du temps B1</a></li>
                    <li><a href="EmploiDuTempsB2.php">Emploi du temps B2</a></li>
                    <li><a href="EmploiDuTempsB3.php">Emploi du temps B3</a></li>
                </ul>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="graph.php">
                    <i class="bi bi-graph-up"></i> Graphiques
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="settings.php">
                    <i class="bi bi-gear"></i> Paramètres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </a>
            </li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid">
            <!-- Welcome Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="mb-3">Bienvenue sur CampusFlow</h1>
                    <p class="text-muted">Vue d'ensemble de votre établissement</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <h3 class="mb-3"><i class="bi bi-people"></i> 256</h3>
                        <p class="mb-0">Étudiants inscrits</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <h3 class="mb-3"><i class="bi bi-person-video3"></i> 32</h3>
                        <p class="mb-0">Professeurs</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <h3 class="mb-3"><i class="bi bi-journal-check"></i> 18</h3>
                        <p class="mb-0">Matières</p>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card">
                        <h3 class="mb-3"><i class="bi bi-currency-euro"></i> 45k</h3>
                        <p class="mb-0">Revenus mensuels</p>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-lg-8 mb-3">
                    <div class="dashboard-card p-4">
                        <h5 class="card-title mb-3">Performance académique</h5>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="academicPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="dashboard-card p-4">
                        <h5 class="card-title mb-3">Répartition par filière</h5>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="distributionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle
        document.querySelector('.sidebar-toggle').addEventListener('click', () => {
            document.querySelector('.sidebar').classList.toggle('active');
        });

        // Responsive handling
        function handleResize() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').classList.remove('active');
            }
        }

        window.addEventListener('resize', handleResize);

        document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const menu = toggle.nextElementSibling;
                const isExpanded = menu.classList.contains('show');

                // Close all other dropdowns
                document.querySelectorAll('.dropdown-menu').forEach(otherMenu => {
                    if (otherMenu !== menu) {
                        otherMenu.classList.remove('show');
                    }
                });

                // Toggle current dropdown
                menu.classList.toggle('show', !isExpanded);
            });
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.dropdown-toggle') && !e.target.closest('.dropdown-menu')) {
                document.querySelectorAll('.dropdown-menu').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });

        // Chart.js for Academic Performance
        const academicPerformanceCtx = document.getElementById('academicPerformanceChart').getContext('2d');
        new Chart(academicPerformanceCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Performance',
                    data: [12, 19, 3, 5, 2, 3],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Chart.js for Distribution by Field
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'pie',
            data: {
                labels: ['Field 1', 'Field 2', 'Field 3'],
                datasets: [{
                    label: 'Distribution',
                    data: [30, 40, 30],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    </script>
</body>

</html>
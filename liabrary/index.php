<?php
include 'config.php';

$totalBooks = $conn->query("SELECT SUM(quantity) as total FROM books")->fetch_assoc()['total'] ?? 0;
$totalUsers = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'] ?? 0;
$issuedBooks = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'issued'")->fetch_assoc()['total'] ?? 0;
$overdueBooks = $conn->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'overdue'")->fetch_assoc()['total'] ?? 0;
$availableBooks = $conn->query("SELECT SUM(available) as total FROM books")->fetch_assoc()['total'] ?? 0;

$recentTransactions = $conn->query("SELECT t.*, b.title, u.name as user_name 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-book-open"></i> Library System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="books.php"><i class="fas fa-book"></i> Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="transactions.php"><i class="fas fa-exchange-alt"></i> Issue/Return</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-chart-line"></i> Dashboard</h2>
        
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-book"></i> Total Books</h5>
                        <h2><?php echo $totalBooks; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-users"></i> Total Users</h5>
                        <h2><?php echo $totalUsers; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-hand-holding"></i> Issued Books</h5>
                        <h2><?php echo $issuedBooks; ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5><i class="fas fa-exclamation-triangle"></i> Overdue</h5>
                        <h2><?php echo $overdueBooks; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-history"></i> Recent Transactions</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Book</th>
                                    <th>User</th>
                                    <th>Issue Date</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $recentTransactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['title']; ?></td>
                                    <td><?php echo $row['user_name']; ?></td>
                                    <td><?php echo $row['issue_date']; ?></td>
                                    <td><?php echo $row['due_date']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['status']=='returned'?'success':
                                            ($row['status']=='overdue'?'danger':'warning'); 
                                        ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if($recentTransactions->num_rows == 0): ?>
                                <tr><td colspan="5" class="text-center">No transactions yet</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

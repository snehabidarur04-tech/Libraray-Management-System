<?php
include 'config.php';

$message = '';
$messageType = '';

if (isset($_POST['issue_book'])) {
    $book_id = $_POST['book_id'];
    $user_id = $_POST['user_id'];
    $issue_date = $_POST['issue_date'];
    $due_date = $_POST['due_date'];
    
    $checkBook = $conn->query("SELECT available FROM books WHERE id=$book_id");
    $bookData = $checkBook->fetch_assoc();
    
    if ($bookData['available'] > 0) {
        $sql = "INSERT INTO transactions (book_id, user_id, issue_date, due_date) VALUES ($book_id, $user_id, '$issue_date', '$due_date')";
        
        if ($conn->query($sql)) {
            $conn->query("UPDATE books SET available = available - 1 WHERE id=$book_id");
            $message = "Book issued successfully!";
            $messageType = "success";
        } else {
            $message = "Error: " . $conn->error;
            $messageType = "danger";
        }
    } else {
        $message = "Book not available!";
        $messageType = "danger";
    }
}

if (isset($_POST['return_book'])) {
    $id = $_POST['id'];
    $return_date = $_POST['return_date'];
    $fine = $_POST['fine'];
    $remarks = $_POST['remarks'];
    
    $trans = $conn->query("SELECT book_id FROM transactions WHERE id=$id")->fetch_assoc();
    $book_id = $trans['book_id'];
    
    $sql = "UPDATE transactions SET return_date='$return_date', status='returned', fine=$fine, remarks='$remarks' WHERE id=$id";
    
    if ($conn->query($sql)) {
        $conn->query("UPDATE books SET available = available + 1 WHERE id=$book_id");
        $message = "Book returned successfully!";
        $messageType = "success";
    }
}

$conn->query("UPDATE transactions SET status='overdue' WHERE due_date < CURDATE() AND status='issued'");

$transactions = $conn->query("SELECT t.*, b.title, u.name as user_name, u.email as user_email 
    FROM transactions t 
    JOIN books b ON t.book_id = b.id 
    JOIN users u ON t.user_id = u.id 
    ORDER BY t.created_at DESC");

$books = $conn->query("SELECT * FROM books WHERE available > 0");
$users = $conn->query("SELECT * FROM users WHERE status = 'active'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue/Return - Library System</title>
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
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="books.php"><i class="fas fa-book"></i> Books</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link active" href="transactions.php"><i class="fas fa-exchange-alt"></i> Issue/Return</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4"><i class="fas fa-exchange-alt"></i> Issue / Return Books</h2>
        
        <?php if($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5><i class="fas fa-hand-holding"></i> Issue Book</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label>Select Book</label>
                                <select name="book_id" class="form-control" required>
                                    <option value="">-- Select Book --</option>
                                    <?php while($book = $books->fetch_assoc()): ?>
                                    <option value="<?php echo $book['id']; ?>"><?php echo $book['title']; ?> (<?php echo $book['available']; ?> available)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Select User</label>
                                <select name="user_id" class="form-control" required>
                                    <option value="">-- Select User --</option>
                                    <?php while($user = $users->fetch_assoc()): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?> (<?php echo $user['email']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Issue Date</label>
                                    <input type="date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Due Date</label>
                                    <input type="date" name="due_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                                </div>
                            </div>
                            <button type="submit" name="issue_book" class="btn btn-success w-100">
                                <i class="fas fa-check"></i> Issue Book
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-list"></i> Transaction History</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Book</th>
                                        <th>User</th>
                                        <th>Issue Date</th>
                                        <th>Due Date</th>
                                        <th>Return Date</th>
                                        <th>Fine</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo substr($row['title'], 0, 20); ?>...</td>
                                        <td><?php echo $row['user_name']; ?></td>
                                        <td><?php echo $row['issue_date']; ?></td>
                                        <td><?php echo $row['due_date']; ?></td>
                                        <td><?php echo $row['return_date'] ?? '-'; ?></td>
                                        <td>$<?php echo number_format($row['fine'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['status']=='returned'?'success':
                                                ($row['status']=='overdue'?'danger':'warning'); 
                                            ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($row['status'] != 'returned'): ?>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#returnModal<?php echo $row['id']; ?>">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="returnModal<?php echo $row['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-info text-white">
                                                    <h5 class="modal-title">Return Book</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                        <p><strong>Book:</strong> <?php echo $row['title']; ?></p>
                                                        <p><strong>User:</strong> <?php echo $row['user_name']; ?></p>
                                                        <p><strong>Issue Date:</strong> <?php echo $row['issue_date']; ?></p>
                                                        <p><strong>Due Date:</strong> <?php echo $row['due_date']; ?></p>
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label>Return Date</label>
                                                                <input type="date" name="return_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label>Fine ($)</label>
                                                                <input type="number" name="fine" class="form-control" step="0.01" value="0.00">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label>Remarks</label>
                                                            <textarea name="remarks" class="form-control" rows="2"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" name="return_book" class="btn btn-info">Return Book</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

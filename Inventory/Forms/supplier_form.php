<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Supplier</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            background-color: white; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); 
            padding: 20px; 
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Add New Supplier</h2>
            <form action="add_supplier.php" method="post">
                <div class="mb-3">
                    <label for="supplier_name" class="form-label">Supplier Name:</label>
                    <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                </div>
                <div class="mb-3">
                    <label for="contact_number" class="form-label">Contact Number:</label>
                    <input type="text" class="form-control" id="contact_number" name="contact_number" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address:</label>
                    <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="lead_time" class="form-label">Lead Time (days):</label>
                    <input type="number" class="form-control" id="lead_time" name="lead_time" min="0" required>
                </div>
                <button type="submit" class="btn btn-primary">Save</button>
                <a href="..\src\supplier_list.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

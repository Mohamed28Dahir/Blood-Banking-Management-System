<?php
/**
 * Blood Donation Management System
 * Manage Blood Stock - Admin Panel
 */

$page_title = "Manage Blood Stock";
$include_admin_css = true;
include '../includes/header.php';

requireAdmin();

// Handle stock update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_stock'])) {
    $blood_group = sanitize($_POST['blood_group']);
    $units = (int)$_POST['units_available'];
    
    if ($units >= 0) {
        $conn->query("UPDATE blood_stock SET units_available = $units WHERE blood_group = '$blood_group'");
        $_SESSION['success'] = "Blood stock updated successfully!";
        redirect('manage_blood_stock.php');
    }
}

// Fetch blood stock
$blood_stock = $conn->query("SELECT * FROM blood_stock ORDER BY blood_group");
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>

        <div class="col-md-9 col-lg-10 admin-content">
            <div class="admin-header">
                <h1><i class="fas fa-database"></i> Blood Stock Management</h1>
                <p class="text-muted mb-0">Monitor and update blood inventory</p>
            </div>

            <?php echo displaySuccess(); ?>
            <?php echo displayError(); ?>

            <!-- Blood Stock Cards -->
            <div class="row g-4 mb-4">
                <?php while($stock = $blood_stock->fetch_assoc()): 
                    $stock_class = 'out-of-stock';
                    $icon_class = 'text-danger';
                    if ($stock['units_available'] > 5) {
                        $stock_class = 'available';
                        $icon_class = 'text-success';
                    } elseif ($stock['units_available'] > 0) {
                        $stock_class = 'low-stock';
                        $icon_class = 'text-warning';
                    }
                ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card">
                            <div class="card-body text-center">
                                <i class="fas fa-tint fa-3x mb-3 <?php echo $icon_class; ?>"></i>
                                <h2 class="mb-2"><?php echo $stock['blood_group']; ?></h2>
                                <h3 class="text-primary"><?php echo $stock['units_available']; ?></h3>
                                <p class="text-muted mb-3">Units Available</p>
                                <span class="badge <?php echo getBloodGroupBadge($stock['blood_group']); ?> mb-3">
                                    <?php 
                                    if ($stock['units_available'] > 5) echo "Adequate Stock";
                                    elseif ($stock['units_available'] > 0) echo "Low Stock";
                                    else echo "Out of Stock";
                                    ?>
                                </span>
                                <br>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#updateStockModal<?php echo str_replace('+', 'Plus', str_replace('-', 'Minus', $stock['blood_group'])); ?>">
                                    <i class="fas fa-edit"></i> Update Stock
                                </button>
                                <br>
                                <small class="text-muted">
                                    Last updated: <?php echo formatDateTime($stock['last_updated']); ?>
                                </small>
                            </div>
                        </div>
                        
                        <!-- Update Stock Modal -->
                        <div class="modal fade" id="updateStockModal<?php echo str_replace('+', 'Plus', str_replace('-', 'Minus', $stock['blood_group'])); ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Update <?php echo $stock['blood_group']; ?> Stock</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST">
                                        <div class="modal-body">
                                            <input type="hidden" name="blood_group" value="<?php echo $stock['blood_group']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Current Stock</label>
                                                <input type="text" class="form-control" value="<?php echo $stock['units_available']; ?> units" disabled>
                                            </div>
                                            
                                            <div class="mb-3">
                                                <label class="form-label">New Stock Level *</label>
                                                <input type="number" class="form-control" name="units_available" min="0" value="<?php echo $stock['units_available']; ?>" required>
                                                <small class="text-muted">Set the total number of units available</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_stock" class="btn btn-primary">Update Stock</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Stock Summary -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-chart-bar"></i> Stock Summary
                        </div>
                        <div class="card-body">
                            <?php 
                            $blood_stock2 = $conn->query("SELECT * FROM blood_stock ORDER BY blood_group");
                            $total_units = 0;
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Blood Group</th>
                                            <th>Units Available</th>
                                            <th>Status</th>
                                            <th>Last Updated</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($stock2 = $blood_stock2->fetch_assoc()): 
                                            $total_units += $stock2['units_available'];
                                        ?>
                                            <tr>
                                                <td>
                                                    <span class="badge <?php echo getBloodGroupBadge($stock2['blood_group']); ?> fs-6">
                                                        <?php echo $stock2['blood_group']; ?>
                                                    </span>
                                                </td>
                                                <td><strong><?php echo $stock2['units_available']; ?></strong> units</td>
                                                <td>
                                                    <?php if ($stock2['units_available'] > 5): ?>
                                                        <span class="badge bg-success">Adequate</span>
                                                    <?php elseif ($stock2['units_available'] > 0): ?>
                                                        <span class="badge bg-warning text-dark">Low Stock</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Out of Stock</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo formatDateTime($stock2['last_updated']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateStockModal<?php echo str_replace('+', 'Plus', str_replace('-', 'Minus', $stock2['blood_group'])); ?>">
                                                        <i class="fas fa-edit"></i> Update
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                        <tr class="table-active">
                                            <td><strong>TOTAL</strong></td>
                                            <td colspan="4"><strong><?php echo $total_units; ?></strong> units</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$hide_navbar = true;
include '../includes/footer.php';
?>

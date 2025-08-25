<?php
// /modules/stock_management.php
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Stock Dashboard</h1>
</div>

<!-- Dashboard Summary Cards -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Low Stock Items</h5>
                <p class="card-text fs-2" id="low-stock-count">0</p>
                <a href="#" class="text-white stretched-link" data-filter="low">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Out of Stock Items</h5>
                <p class="card-text fs-2" id="zero-stock-count">0</p>
                <a href="#" class="text-white stretched-link" data-filter="zero">View Details</a>
            </div>
        </div>
    </div>
</div>

<!-- Product Grid -->
<div class="row" id="product-grid">
    <div class="col-12 text-center p-5">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- MODALS SECTION -->

<!-- Add Stock Modal -->
<div class="modal fade" id="addStockModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Add Stock for: <span id="addStockProductName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form id="addStockForm" data-action="addStock" data-handler="ajax/stock_handler.php">
    <div class="modal-body">
        <input type="hidden" id="addStockProductId" name="product_id">
        <div class="mb-3"><label for="add_quantity" class="form-label">Quantity to Add</label><input type="number" class="form-control" id="add_quantity" name="quantity" min="1" required></div>
        <div id="add-serial-number-inputs-container" class="d-none"><hr><h6>Enter Serial Numbers</h6><div id="add-serial-number-inputs"></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Stock</button></div>
</form>
</div></div></div>

<!-- Remove Stock Modal -->
<div class="modal fade" id="removeStockModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Remove Stock for: <span id="removeStockProductName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form id="removeStockForm" data-action="removeStock" data-handler="ajax/stock_handler.php">
    <div class="modal-body">
        <input type="hidden" id="removeStockProductId" name="product_id">
        <div class="mb-3"><label for="remove_quantity" class="form-label">Quantity to Remove</label><input type="number" class="form-control" id="remove_quantity" name="quantity" min="1" required></div>
        <div id="remove-serial-number-inputs-container" class="d-none"><hr><h6>Select Serial Numbers to Remove</h6><div id="remove-serial-number-inputs" style="max-height: 200px; overflow-y: auto;"></div></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-danger">Confirm Removal</button></div>
</form>
</div></div></div>
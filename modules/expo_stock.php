<?php
// /modules/expo_stock.php - FINAL VERSION with Print Feature
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Expo Management</h1>
    <div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#moveToExpoModal">+ Move Stock to Expo</button>
        <button class="btn btn-secondary" id="printExpoSummaryBtn"><i class="bi bi-printer"></i> Print Summary</button> <!-- NEW PRINT BUTTON -->
        <button class="btn btn-success" id="returnAllFromExpoBtn">✓ End Expo & Return All</button>
    </div>
</div>

<!-- This is the wrapper for the printable area -->
<div id="print-section">
    <!-- This title will only be visible when printing -->
    <h2 id="print-title" style="display: none;">Expo Stock Summary - <?php echo date("Y-m-d H:i"); ?></h2>

    <div class="card">
        <div class="card-header">Current Stock at Expo</div>
        <div class="card-body"><div class="table-responsive"><table class="table">
            <thead><tr><th>Product Name</th><th>Quantity in Expo</th><th>Actions</th></tr></thead>
            <tbody id="expo-stock-list"></tbody>
        </table></div></div>
    </div>
</div>


<!-- MODALS SECTION -->
<!-- (All modals remain the same) -->
<!-- ... -->

<!-- MODALS SECTION -->
<div class="modal fade" id="moveToExpoModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Move Stock to Expo</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <!-- ADDED data-* attributes -->
    <form id="moveToExpoForm" data-action="moveToExpo" data-handler="ajax/expo_handler.php">
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Select Product</label><select class="form-select" id="expo_product_select" name="product_id" required><option value="">-- Loading... --</option><?php foreach ($pdo->query("SELECT p.id, p.name, p.requires_serial FROM products p JOIN stock s ON p.id = s.product_id WHERE s.quantity > 0 ORDER BY p.name") as $row) { echo "<option value='{$row['id']}' data-requires-serial='{$row['requires_serial']}'>{$row['name']}</option>"; } ?></select></div>
            <div class="mb-3"><label class="form-label">Quantity to Move</label><input type="number" class="form-control" name="quantity" min="1" required></div>
            <div id="expo-serial-select-container" class="d-none"><hr><h6>Select Serial Numbers to Move</h6><div id="expo-serial-select-inputs" style="max-height: 200px; overflow-y: auto;"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Confirm Move</button></div>
    </form>
    </div></div>
</div>

<div class="modal fade" id="returnItemModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Return Item: <span id="returnItemName"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <!-- ADDED data-* attributes -->
    <form id="returnItemForm" data-action="returnItemFromExpo" data-handler="ajax/expo_handler.php">
        <div class="modal-body">
            <input type="hidden" id="returnProductId" name="product_id">
            <div class="mb-3"><label class="form-label">Quantity to Return</label><input type="number" class="form-control" id="returnQuantity" name="quantity" min="1" required></div>
            <div id="return-serial-select-container" class="d-none"><hr><h6>Select Serials to Return</h6><div id="return-serial-select-inputs" style="max-height: 200px; overflow-y: auto;"></div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Confirm Return</button></div>
    </form>
    </div></div>
</div>

<div class="modal fade" id="expoBillingModal" tabindex="-1" data-bs-backdrop="static">
    <!-- (The billing modal doesn't need data-* on its forms because its logic is more complex) -->
    <!-- ... (rest of billing modal HTML) ... -->
    <div class="modal-dialog modal-lg"><div class="modal-content">...</div></div>
</div>
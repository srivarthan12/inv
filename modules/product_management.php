<?php
// modules/product_management.php - FINAL and COMPLETE VERSION
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Product Management</h1>
</div>

<div class="row">
    <!-- Attributes Column -->
    <div class="col-12 col-md-4">
        
        <!-- Categories Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Categories</h5><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add</button></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($pdo->query("SELECT id, name FROM categories ORDER BY name") as $row): ?>
                    <li class='list-group-item d-flex justify-content-between align-items-center'>
                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                        <a href="#" class="btn-delete-item text-danger" data-id="<?php echo $row['id']; ?>" data-table="categories"><i class="bi bi-trash"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Product Types Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Product Types</h5><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addProductTypeModal">+ Add</button></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($pdo->query("SELECT pt.id, pt.name, c.name as category_name FROM product_types pt LEFT JOIN categories c ON pt.category_id = c.id ORDER BY pt.name") as $row): ?>
                    <li class='list-group-item d-flex justify-content-between align-items-center'>
                        <div><span><?php echo htmlspecialchars($row['name']); ?></span><br><small class="text-muted"><?php echo htmlspecialchars($row['category_name'] ?? 'N/A'); ?></small></div>
                        <a href="#" class="btn-delete-item text-danger" data-id="<?php echo $row['id']; ?>" data-table="product_types"><i class="bi bi-trash"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <!-- Variants Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Variants</h5><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVariantModal">+ Add</button></div>
             <ul class="list-group list-group-flush">
                <?php foreach ($pdo->query("SELECT v.id, v.name, pt.name as type_name FROM variants v LEFT JOIN product_types pt ON v.type_id = pt.id ORDER BY v.name") as $row): ?>
                    <li class='list-group-item d-flex justify-content-between align-items-center'>
                       <div><span><?php echo htmlspecialchars($row['name']); ?></span><br><small class="text-muted"><?php echo htmlspecialchars($row['type_name'] ?? 'N/A'); ?></small></div>
                        <a href="#" class="btn-delete-item text-danger" data-id="<?php echo $row['id']; ?>" data-table="variants"><i class="bi bi-trash"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Brands Card -->
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center"><h5 class="mb-0">Brands</h5><button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">+ Add</button></div>
            <ul class="list-group list-group-flush">
                <?php foreach ($pdo->query("SELECT id, name FROM brands ORDER BY name") as $row): ?>
                     <li class='list-group-item d-flex justify-content-between align-items-center'>
                        <span><?php echo htmlspecialchars($row['name']); ?></span>
                        <a href="#" class="btn-delete-item text-danger" data-id="<?php echo $row['id']; ?>" data-table="brands"><i class="bi bi-trash"></i></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Products Column -->
    <div class="col-12 col-md-8">
        <div class="card">
             <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">All Products</h5>
                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">+ Create Product</button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Attributes</th>
                                <th>Prices</th>
                                <th>Stock</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="product-list-container">
                            <!-- JS will inject rows here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALS SECTION -->
<div class="modal fade" id="addCategoryModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Category</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="addCategoryForm" data-action="createCategory" data-handler="ajax/product_handler.php"><div class="modal-body"><label class="form-label">Category Name</label><input type="text" class="form-control" name="category_name" required></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<div class="modal fade" id="addBrandModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Brand</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="addBrandForm" data-action="createBrand" data-handler="ajax/product_handler.php"><div class="modal-body"><label class="form-label">Brand Name</label><input type="text" class="form-control" name="brand_name" required></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<div class="modal fade" id="addProductTypeModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Product Type</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="addProductTypeForm" data-action="createProductType" data-handler="ajax/product_handler.php"><div class="modal-body"><div class="mb-3"><label class="form-label">Parent Category</label><select class="form-select" name="category_id" required><option value="">-- Select --</option><?php foreach ($pdo->query("SELECT id, name FROM categories ORDER BY name") as $row) echo "<option value='{$row['id']}'>{$row['name']}</option>"; ?></select></div><div class="mb-3"><label class="form-label">Product Type Name</label><input type="text" class="form-control" name="type_name" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<div class="modal fade" id="addVariantModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Variant</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="addVariantForm" data-action="createVariant" data-handler="ajax/product_handler.php"><div class="modal-body"><div class="mb-3"><label class="form-label">Parent Category</label><select class="form-select" id="variant_category_select" required><option value="">-- Select --</option><?php foreach ($pdo->query("SELECT id, name FROM categories ORDER BY name") as $row) echo "<option value='{$row['id']}'>{$row['name']}</option>"; ?></select></div><div class="mb-3"><label class="form-label">Parent Product Type</label><select class="form-select" id="variant_type_id" name="type_id" required disabled><option value="">-- Select Category First --</option></select></div><div class="mb-3"><label class="form-label">Variant Name</label><input type="text" class="form-control" name="variant_name" required></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save</button></div></form></div></div></div>
<div class="modal fade" id="addProductModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Create New Product</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form id="addProductForm" data-action="createProduct" data-handler="ajax/product_handler.php"><div class="modal-body"><div class="row"><div class="col-md-6"><div class="mb-3"><label class="form-label">Category</label><select class="form-select" id="product_category" name="category_id" required><option value="">-- Select --</option><?php foreach ($pdo->query("SELECT id, name FROM categories ORDER BY name") as $row) echo "<option value='{$row['id']}'>{$row['name']}</option>"; ?></select></div><div class="mb-3"><label class="form-label">Product Type</label><select class="form-select" id="product_type" name="type_id" required disabled><option value="">-- Select Category First --</option></select></div><div class="mb-3"><label class="form-label">Variant</label><select class="form-select" id="product_variant" name="variant_id" required disabled><option value="">-- Select Type First --</option></select></div><div class="mb-3"><label class="form-label">Brand</label><select class="form-select" id="product_brand" name="brand_id" required><option value="">-- Select --</option><?php foreach ($pdo->query("SELECT id, name FROM brands ORDER BY name") as $row) echo "<option value='{$row['id']}'>{$row['name']}</option>"; ?></select></div></div><div class="col-md-6"><div class="mb-3"><label class="form-label">Product Name</label><input type="text" class="form-control" name="product_name" required></div><div class="row"><div class="col-6"><label class="form-label">MRP</label><input type="number" step="0.01" class="form-control" name="mrp"></div><div class="col-6"><label class="form-label">Sales Price</label><input type="number" step="0.01" class="form-control" name="sales_price" required></div></div><div class="mb-3 mt-3"><label class="form-label">Supplier Price</label><input type="number" step="0.01" class="form-control" name="supplier_price"></div><div class="mb-3"><label class="form-label">Minimum Stock</label><input type="number" class="form-control" name="min_stock" value="0" required></div><div class="mb-3"><label class="form-label">Supplier</label><select class="form-select" name="supplier_id"><option value="">-- Select --</option><?php foreach ($pdo->query("SELECT id, name FROM suppliers ORDER BY name") as $row) echo "<option value='{$row['id']}'>{$row['name']}</option>"; ?></select></div><div class="form-check form-switch mt-4"><input class="form-check-input" type="checkbox" role="switch" name="requires_serial"><label class="form-check-label">Requires Serial Number</label></div></div></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button><button type="submit" class="btn btn-primary">Save Product</button></div></form></div></div></div>
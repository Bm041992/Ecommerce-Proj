<Doctype html>
<html>
<head>
    <title>Dashboard</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.min.css">
</head>
<body>
    <div class="container">
        
        <div class=" mt-3">
            <h1>Dashboard</h1>
            <div class="text-end">
                <a href="{{ route('upload.index') }}" class="btn btn-primary">Upload CSV</a>
            </div>
        </div>
        <div class="card mt-3">
            <div class="card-header">
                <h2>Products</h2>
            </div>
            <div class="card-body">
                <table id="productsTable" class="table">
                    <thead>
                        <tr>
                        <th>Title</th>
                        <th>Variant SKU</th>
                        <th>Shopify Product ID</th>
                        <th>Status</th>
                        <th>Error Message</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        order: [],
        ajax: "{{ route('dashboard') }}",
        columns: [
            { data: 'title', name: 'title' },
            { data: 'variant_sku', name: 'variant_sku',className: 'text-center' },
            { data: 'shopify_product_id', name: 'shopify_product_id',className: 'text-center', orderable: false,sortable: false },
            { data: 'status', name: 'status',className: 'text-center' },
            { data: 'error_message', name: 'error_message', orderable: false, searchable: false,sortable: false },
        ]
    });
</script>
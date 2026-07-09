<!DOCTYPE html>
<html>
<head>
    <title>CSV Upload</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .error {
            color: red;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="mt-2 mb-2">
            <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
        </div>
        <div class="card">
            <div class="card-header">
                Upload CSV
            </div>
            
            <div class="card-body">
                
                <form  method="POST" enctype="multipart/form-data" id="uploadForm">
                    <div class="mb-3">
                        <label class="form-label">CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept="text/csv">
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary">
                        Upload
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.21.0/dist/additional-methods.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-loading-overlay/2.1.7/loadingoverlay.min.js"></script>

<script>
    $(document).ready(function() {
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.validator.addMethod("filesize", function(value, element, param) {

            if (element.files.length === 0)
                return false;

            return element.files[0].size <= param;

        }, "Maximum file size is 2 MB.");
 
        $('#uploadForm').validate({
            rules: {
                csv_file: {
                    required: true,
                    extension: "csv",
                    filesize: 2097152 // 2 MB in bytes
                }
            },
            messages: {
                csv_file: {
                    required: "Please select a CSV file.",
                    extension: "Only CSV files are allowed."
                }
            },
            submitHandler: function(form) {
               
                jQuery.ajax({
                    url: '{{ route("upload.store") }}',
                    type: 'POST',
                    data: new FormData(form),
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    beforeSend: function() {
                        //$.LoadingOverlay('#uploadForm',"show");
                        $("#uploadForm").LoadingOverlay("show");
                    },
                    success: function(response) {
                        // $.LoadingOverlay('#uploadForm',"hide");
                        $("#uploadForm").LoadingOverlay("hide");
                        if (response.success) {
                            form.reset();
                        } 
                        alert(response.message);
                    },
                    error: function(xhr) {
                        $("#uploadForm").LoadingOverlay("hide");
                        alert('An error occurred while uploading the file.');
                    }
                });
            }
        });
    });
</script>
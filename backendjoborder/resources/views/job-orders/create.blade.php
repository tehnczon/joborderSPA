<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Create Job Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
        crossorigin="anonymous"></script>
</head>

<body>
    <div class="container mt-4">
        <h2>Create Job Order</h2>

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Success message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="row">
            <form action="{{ route('job-orders.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="customer_type" class="form-label">Customer Type</label>
                    <select name="customer_type" id="customer_type" class="form-control" required>
                        <option value="customer">Customer</option>
                        <option value="technician-customer">Technician Customer</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="customer_name" class="form-label">Customer Name</label>
                    <input type="text" class="form-control" name="customer_name" required>
                </div>

                <div class="mb-3">
                    <label for="laptop_model" class="form-label">Laptop Model</label>
                    <input type="text" class="form-control" name="laptop_model" required>
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="Pending">Pending</option>
                        <option value="In Progress">In Progress</option>
                        <option value="Completed">Completed</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="pullout_date" class="form-label">Pullout Date</label>
                    <input type="date" class="form-control" name="pullout_date">
                </div>

                <div class="mb-3">
                    <label for="ram" class="form-label">RAM</label>
                    <input type="text" class="form-control" name="ram">
                </div>

                <div class="mb-3">
                    <label for="ssd" class="form-label">SSD</label>
                    <input type="text" class="form-control" name="ssd">
                </div>

                <div class="mb-3">
                    <label for="hdd" class="form-label">HDD</label>
                    <input type="text" class="form-control" name="hdd">
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="has_battery" id="has_battery" value="1" class="form-check-input">
                    <label for="has_battery" class="form-check-label">Has Battery</label>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="has_wifi_card" id="has_wifi_card" value="1" class="form-check-input">
                    <label for="has_wifi_card" class="form-check-label">Has WiFi Card</label>
                </div>

                <div class="mb-3">
                    <label for="others" class="form-label">Others</label>
                    <textarea class="form-control" name="others"></textarea>
                </div>

                <div class="mb-3">
                    <label for="without" class="form-label">Without</label>
                    <textarea class="form-control" name="without"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Create Job Order</button>
            </form>
        </div>
    </div>
</body>

</html>

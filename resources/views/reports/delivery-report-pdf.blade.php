<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delivery Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .download-section {
            text-align: center;
            padding: 15px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            display: no-print;
        }
        .download-btn {
            background-color: #2563eb;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .download-btn:hover {
            background-color: #1d4ed8;
        }
        .container {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1F2937;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 26px;
            color: #1F2937;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 12px;
        }
        .filters-info {
            background-color: #f3f4f6;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 12px;
            color: #555;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            text-align: center;
            padding: 15px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-radius: 4px;
        }
        .stat-label {
            font-size: 11px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: bold;
        }
        .stat-value {
            font-size: 22px;
            font-weight: bold;
            color: #1F2937;
            margin-top: 5px;
        }
        .stat-value.green {
            color: #15803d;
        }
        .stat-value.orange {
            color: #ca8a04;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        thead {
            background-color: #1F2937;
            color: white;
        }
        th {
            padding: 12px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        tbody tr:nth-child(odd) {
            background-color: #f9fafb;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-received {
            background-color: #dcfce7;
            color: #15803d;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #b45309;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #999;
        }
        @page {
            size: A4;
            margin: 10mm;
        }
        @media print {
            .download-section {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="download-section">
        <button class="download-btn" onclick="window.print()">⬇️ Download as PDF</button>
    </div>

    <div class="container">
        <div class="header">
            <h1>Delivery Report</h1>
            <p>Generated on {{ $generatedAt }}</p>
        </div>

        <div class="filters-info">
            <strong>Report Filters:</strong> 
            @if($filters['from_date'] && $filters['to_date'])
                <span>Date Range: {{ $filters['from_date'] }} to {{ $filters['to_date'] }}</span>
                @if($filters['vendor_id'] || ($filters['status'] !== null && $filters['status'] !== '')) | @endif
            @endif
            @if($filters['vendor_id'])
                <span>Vendor ID: {{ $filters['vendor_id'] }}</span>
                @if($filters['status'] !== null && $filters['status'] !== '') | @endif
            @endif
            @if($filters['status'] !== null && $filters['status'] !== '')
                <span>Status: {{ ucfirst($filters['status']) }}</span>
            @endif
            @if(!$filters['from_date'] && !$filters['to_date'] && !$filters['vendor_id'] && ($filters['status'] === null || $filters['status'] === ''))
                No filters applied
            @endif
        </div>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Deliveries</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Received</div>
                <div class="stat-value green">{{ $stats['received'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Pending</div>
                <div class="stat-value orange">{{ $stats['pending'] }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Received Rate</div>
                <div class="stat-value">{{ $stats['received_percentage'] }}%</div>
            </div>
        </div>

        <!-- Table -->
        <table>
            <thead>
                <tr>
                    <th>DO Number</th>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Delivery Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deliveryOrders as $order)
                    <tr>
                        <td style="font-weight: bold;">{{ $order->do_number }}</td>
                        <td>{{ $order->purchaseOrder->order_number }}</td>
                        <td>{{ $order->purchaseOrder->vendor->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') }}</td>
                        <td>
                            <span class="status-badge {{ $order->is_received ? 'status-received' : 'status-pending' }}">
                                {{ $order->is_received ? 'Received' : 'Pending' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: #999;">No delivery orders found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="footer">
            <p>This is an automated report generated by the system.</p>
        </div>
    </div>
</body>
</html>

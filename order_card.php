<div class="order-card animate-fade-in <?php echo $delay_class; ?>">
    <div class="order-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Order #<?php echo $order['order_id']; ?></h5>
            <small class="text-white-50">Placed on <?php echo date('d M Y', strtotime($order['order_date'])); ?></small>
        </div>
        <span class="order-status <?php echo getStatusClass($order['status']); ?>">
            <?php echo $order['status']; ?>
        </span>
    </div>
    <div class="order-body">
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <h6 class="mb-2">Payment Details:</h6>
                    <p class="mb-1">
                        <span class="<?php echo getPaymentStatusClass($order['payment_table_status']); ?> me-2">
                            <?php echo $order['payment_table_status'] ?? 'Pending'; ?>
                        </span>
                        <?php echo $order['payment_method'] ?? 'N/A'; ?>
                    </p>
                    <?php if ($order['transaction_id']): ?>
                        <small class="text-muted">Transaction ID: <?php echo $order['transaction_id']; ?></small>
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <h6 class="mb-2">Shipping Address:</h6>
                    <p class="mb-1"><?php echo htmlspecialchars($order['address']); ?></p>
                    <p class="mb-0"><?php echo htmlspecialchars($order['pincode']); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="order-timeline">
                    <?php foreach ($timeline as $step => $details): ?>
                        <div class="timeline-step <?php echo $details['completed'] ? 'completed' : ($step == $order['status'] ? 'active' : ''); ?>">
                            <h6 class="mb-1"><?php echo $step; ?></h6>
                            <p class="timeline-date mb-1">
                                <?php echo $details['completed'] ? 
                                    date('d M, h:i A', strtotime($details['date'])) : 
                                    'Estimated: ' . date('d M', strtotime($details['date'])); ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Order Items Preview -->
        <?php 
        $items_sql = "SELECT oi.*, p.product_name, p.image_url FROM order_items oi 
                     JOIN products p ON oi.product_id = p.product_id
                     WHERE oi.order_id = {$order['order_id']} LIMIT 3";
        $items_result = $conn->query($items_sql);
        ?>
        <?php if ($items_result->num_rows > 0): ?>
            <div class="mt-3 pt-3 border-top">
                <h6>Order Items:</h6>
                <div class="d-flex flex-wrap">
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <div class="me-3 mb-2 d-flex align-items-center">
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                 class="order-product-img me-2">
                            <div>
                                <small class="d-block"><?php echo htmlspecialchars($item['product_name']); ?></small>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if ($items_result->num_rows >= 3): ?>
                        <button class="btn btn-sm btn-outline-primary" 
                                data-bs-toggle="modal" 
                                data-bs-target="#orderDetailsModal"
                                data-order-id="<?php echo $order['order_id']; ?>">
                            View All
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <div>
                <h5 class="mb-0">Total: ₹<?php echo number_format($order['total_amount'], 2); ?></h5>
            </div>
            <div>
                <?php if ($order['status'] != 'Cancelled' && $order['status'] != 'Delivered'): ?>
                    <button class="btn btn-outline-danger btn-action me-2" 
                            data-bs-toggle="modal" 
                            data-bs-target="#cancelOrderModal"
                            data-order-id="<?php echo $order['order_id']; ?>"
                            data-order-total="<?php echo $order['total_amount']; ?>">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </button>
                <?php endif; ?>
                
                <?php if ($order['status'] == 'Delivered'): ?>
                    <button class="btn btn-outline-secondary btn-action me-2">
                        <i class="bi bi-arrow-return-left me-1"></i> Return
                    </button>
                    <button class="btn btn-outline-primary btn-action reorder-btn" 
        data-order-id="<?php echo $order['order_id']; ?>">
    <i class="bi bi-arrow-repeat"></i> Reorder
</button>
                <?php else: ?>
                    <button class="btn btn-outline-primary btn-action">
                        <i class="bi bi-truck me-1"></i> Track
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
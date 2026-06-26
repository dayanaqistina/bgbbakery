package com.bgb.bgbbakery.service;

import com.bgb.bgbbakery.dto.ShopOrderRequest;
import com.bgb.bgbbakery.dto.ShopOrderResponse;
import com.bgb.bgbbakery.entity.BakeryOrder;
import com.bgb.bgbbakery.entity.Customer;
import com.bgb.bgbbakery.entity.Delivery;
import com.bgb.bgbbakery.entity.OrderDetails;
import com.bgb.bgbbakery.entity.Pickup;
import com.bgb.bgbbakery.entity.Product;
import com.bgb.bgbbakery.entity.Worker;
import com.bgb.bgbbakery.repository.BakeryOrderRepository;
import com.bgb.bgbbakery.repository.CustomerRepository;
import com.bgb.bgbbakery.repository.ProductRepository;
import com.bgb.bgbbakery.repository.WorkerRepository;
import org.springframework.stereotype.Service;
import org.springframework.transaction.annotation.Transactional;

import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Service
public class ShopOrderService {

    private final BakeryOrderRepository orderRepository;
    private final CustomerRepository customerRepository;
    private final ProductRepository productRepository;
    private final WorkerRepository workerRepository;

    public ShopOrderService(
            BakeryOrderRepository orderRepository,
            CustomerRepository customerRepository,
            ProductRepository productRepository,
            WorkerRepository workerRepository) {
        this.orderRepository = orderRepository;
        this.customerRepository = customerRepository;
        this.productRepository = productRepository;
        this.workerRepository = workerRepository;
    }

    @Transactional
    public ShopOrderResponse createOrder(ShopOrderRequest request) {
        if (request.getItems() == null || request.getItems().isEmpty()) {
            throw new IllegalArgumentException("Order must contain at least one product.");
        }

        String email = request.getEmail() == null || request.getEmail().isBlank()
                ? "not-provided@bgb.local"
                : request.getEmail();
        Customer customer = new Customer(request.getName(), request.getPhoneNumber(), email);
        customer.setId(customerRepository.findMaxId() + 1);
        customer = customerRepository.save(customer);

        BakeryOrder order = new BakeryOrder();
        order.setCustomer(customer);
        order.setWorker(resolveWorker());
        order.setOrderDate(LocalDate.now());
        order.setStatus("Pending");
        order.setOrderType("Delivery".equalsIgnoreCase(request.getFulfilment()) ? "Delivery" : "Pickup");

        BigDecimal total = BigDecimal.ZERO;
        for (ShopOrderRequest.Item item : request.getItems()) {
            Product product = productRepository.findById(item.getProductId())
                    .orElseThrow(() -> new IllegalArgumentException("Product not found: " + item.getProductId()));
            int quantity = item.getQuantity() == null || item.getQuantity() < 1 ? 1 : item.getQuantity();
            BigDecimal subtotal = product.getPrice().multiply(BigDecimal.valueOf(quantity));

            OrderDetails details = new OrderDetails();
            details.setOrder(order);
            details.setProduct(product);
            details.setSubtotal(subtotal);
            details.setQuantity(quantity);
            order.getOrderDetails().add(details);

            total = total.add(subtotal);
        }

        LocalDate preferredDate = request.getPreferredDate() == null ? LocalDate.now() : request.getPreferredDate();
        LocalDateTime fulfilmentDate = preferredDate.atStartOfDay();
        if ("Delivery".equalsIgnoreCase(request.getFulfilment())) {
            Delivery delivery = new Delivery();
            delivery.setOrder(order);
            delivery.setTrackingNumber("TRK" + System.currentTimeMillis());
            delivery.setDateTime(fulfilmentDate);
            delivery.setAddress(request.getNotes() == null || request.getNotes().isBlank() ? "To be confirmed" : request.getNotes());
            order.setDelivery(delivery);
        } else {
            Pickup pickup = new Pickup();
            pickup.setOrder(order);
            pickup.setDateTime(fulfilmentDate);
            order.setPickup(pickup);
        }

        BakeryOrder saved = orderRepository.save(order);
        return new ShopOrderResponse(saved.getId(), saved.getStatus(), total);
    }

    private Worker resolveWorker() {
        return workerRepository.findAll().stream()
                .findFirst()
                .orElseGet(() -> {
                    Worker worker = new Worker("Default Worker", "0000000000");
                    worker.setId(workerRepository.findMaxId() + 1);
                    return workerRepository.save(worker);
                });
    }
}

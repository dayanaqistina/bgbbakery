package com.bgb.bgbbakery.controller;

import com.bgb.bgbbakery.entity.BakeryOrder;
import com.bgb.bgbbakery.service.BakeryOrderService;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;

@RestController
@RequestMapping("/api/orders")
public class OrderController {

    private final BakeryOrderService orderService;

    public OrderController(BakeryOrderService orderService) {
        this.orderService = orderService;
    }

    @GetMapping
    public List<BakeryOrder> getAll() {
        return orderService.findAll();
    }

    @GetMapping("/{id}")
    public ResponseEntity<BakeryOrder> getById(@PathVariable Long id) {
        BakeryOrder order = orderService.findById(id);
        return order != null ? ResponseEntity.ok(order) : ResponseEntity.notFound().build();
    }

    @PostMapping
    public BakeryOrder create(@RequestBody BakeryOrder order) {
        return orderService.save(order);
    }

    @PutMapping("/{id}")
    public ResponseEntity<BakeryOrder> update(@PathVariable Long id, @RequestBody BakeryOrder order) {
        BakeryOrder existing = orderService.findById(id);
        if (existing == null) {
            return ResponseEntity.notFound().build();
        }
        existing.setOrderDate(order.getOrderDate());
        existing.setStatus(order.getStatus());
        existing.setOrderType(order.getOrderType());
        existing.setCustomer(order.getCustomer());
        existing.setWorker(order.getWorker());
        return ResponseEntity.ok(orderService.save(existing));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> delete(@PathVariable Long id) {
        orderService.deleteById(id);
        return ResponseEntity.noContent().build();
    }
}

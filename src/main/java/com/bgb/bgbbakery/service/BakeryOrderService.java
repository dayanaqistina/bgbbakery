package com.bgb.bgbbakery.service;

import com.bgb.bgbbakery.entity.BakeryOrder;
import com.bgb.bgbbakery.repository.BakeryOrderRepository;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class BakeryOrderService {

    private final BakeryOrderRepository orderRepository;

    public BakeryOrderService(BakeryOrderRepository orderRepository) {
        this.orderRepository = orderRepository;
    }

    public List<BakeryOrder> findAll() {
        return orderRepository.findAll();
    }

    public BakeryOrder findById(Long id) {
        return orderRepository.findById(id).orElse(null);
    }

    public BakeryOrder save(BakeryOrder order) {
        return orderRepository.save(order);
    }

    public void deleteById(Long id) {
        orderRepository.deleteById(id);
    }
}

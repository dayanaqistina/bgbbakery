package com.bgb.bgbbakery.controller;

import com.bgb.bgbbakery.dto.ShopOrderRequest;
import com.bgb.bgbbakery.dto.ShopOrderResponse;
import com.bgb.bgbbakery.service.ShopOrderService;
import java.util.Map;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.ExceptionHandler;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/shop/orders")
public class ShopOrderController {

    private final ShopOrderService shopOrderService;

    public ShopOrderController(ShopOrderService shopOrderService) {
        this.shopOrderService = shopOrderService;
    }

    @PostMapping
    public ResponseEntity<ShopOrderResponse> create(@RequestBody ShopOrderRequest request) {
        return ResponseEntity.ok(shopOrderService.createOrder(request));
    }

    @ExceptionHandler(IllegalArgumentException.class)
    public ResponseEntity<Map<String, String>> badRequest(IllegalArgumentException exception) {
        return ResponseEntity.badRequest().body(Map.of("message", exception.getMessage()));
    }
}

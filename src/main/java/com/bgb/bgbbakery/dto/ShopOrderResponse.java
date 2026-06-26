package com.bgb.bgbbakery.dto;

import java.math.BigDecimal;

public class ShopOrderResponse {

    private Long orderId;
    private String status;
    private BigDecimal total;

    public ShopOrderResponse(Long orderId, String status, BigDecimal total) {
        this.orderId = orderId;
        this.status = status;
        this.total = total;
    }

    public Long getOrderId() {
        return orderId;
    }

    public String getStatus() {
        return status;
    }

    public BigDecimal getTotal() {
        return total;
    }
}

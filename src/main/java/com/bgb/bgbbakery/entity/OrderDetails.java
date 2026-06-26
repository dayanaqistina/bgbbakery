package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "ORDERDETAILS")
@IdClass(OrderDetailsId.class)
public class OrderDetails {

    @Id
    @ManyToOne
    @JoinColumn(name = "ORDER_ID")
    private BakeryOrder order;

    @Id
    @ManyToOne
    @JoinColumn(name = "PRODUCT_ID")
    private Product product;

    @Column(name = "SUBTOTAL", nullable = false, precision = 10, scale = 2)
    private BigDecimal subtotal;

    @Column(name = "QUANTITY", nullable = false, precision = 10)
    private Integer quantity;

    public OrderDetails() {
    }

    public OrderDetails(BigDecimal subtotal, BakeryOrder order, Product product) {
        this.subtotal = subtotal;
        this.order = order;
        this.product = product;
        this.quantity = 1;
    }

    public BigDecimal getSubtotal() {
        return subtotal;
    }

    public void setSubtotal(BigDecimal subtotal) {
        this.subtotal = subtotal;
    }

    public BakeryOrder getOrder() {
        return order;
    }

    public void setOrder(BakeryOrder order) {
        this.order = order;
    }

    public Product getProduct() {
        return product;
    }

    public void setProduct(Product product) {
        this.product = product;
    }

    public Integer getQuantity() {
        return quantity;
    }

    public void setQuantity(Integer quantity) {
        this.quantity = quantity;
    }
}

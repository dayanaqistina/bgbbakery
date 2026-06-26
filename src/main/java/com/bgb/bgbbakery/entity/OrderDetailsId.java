package com.bgb.bgbbakery.entity;

import java.io.Serializable;
import java.util.Objects;

public class OrderDetailsId implements Serializable {

    private Long order;
    private Long product;

    public OrderDetailsId() {
    }

    public OrderDetailsId(Long order, Long product) {
        this.order = order;
        this.product = product;
    }

    public Long getOrder() {
        return order;
    }

    public void setOrder(Long order) {
        this.order = order;
    }

    public Long getProduct() {
        return product;
    }

    public void setProduct(Long product) {
        this.product = product;
    }

    @Override
    public boolean equals(Object o) {
        if (this == o) {
            return true;
        }
        if (!(o instanceof OrderDetailsId that)) {
            return false;
        }
        return Objects.equals(order, that.order) && Objects.equals(product, that.product);
    }

    @Override
    public int hashCode() {
        return Objects.hash(order, product);
    }
}

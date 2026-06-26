package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.math.BigDecimal;

@Entity
@Table(name = "PRODUCT")
public class Product {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "PRODUCT_ID", precision = 10)
    private Long id;

    @Column(name = "PRODUCT_NAME", nullable = false, length = 50)
    private String name;

    @Column(name = "PRODUCT_DESC", nullable = false, length = 200)
    private String description;

    @Column(name = "FLAVOUR_TOPPING", length = 100)
    private String flavourTopping;

    @Transient
    private Integer stockQuantity;

    @Column(name = "PRICE", nullable = false, precision = 8, scale = 2)
    private BigDecimal price;

    public Product() {
    }

    public Product(String name, String flavourTopping, Integer stockQuantity, BigDecimal price) {
        this.name = name;
        this.description = flavourTopping == null ? "Freshly baked to order." : flavourTopping;
        this.flavourTopping = flavourTopping;
        this.stockQuantity = stockQuantity;
        this.price = price;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public String getFlavourTopping() {
        return flavourTopping;
    }

    public String getDescription() {
        return description;
    }

    public void setDescription(String description) {
        this.description = description;
    }

    public void setFlavourTopping(String flavourTopping) {
        this.flavourTopping = flavourTopping;
    }

    public Integer getStockQuantity() {
        return stockQuantity;
    }

    public void setStockQuantity(Integer stockQuantity) {
        this.stockQuantity = stockQuantity;
    }

    public BigDecimal getPrice() {
        return price;
    }

    public void setPrice(BigDecimal price) {
        this.price = price;
    }
}

package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.util.ArrayList;
import java.util.List;

@Entity
@Table(name = "CUSTOMER")
public class Customer {

    @Id
    @Column(name = "CUST_ID", precision = 10)
    private Long id;

    @Column(name = "CUST_NAME", nullable = false, length = 50)
    private String name;

    @Column(name = "CUST_NOPHONE", nullable = false, length = 15)
    private String phoneNumber;

    @Column(name = "CUST_EMAIL", nullable = false, length = 50)
    private String email;

    @OneToMany(mappedBy = "customer", cascade = CascadeType.ALL, orphanRemoval = true)
    private List<BakeryOrder> orders = new ArrayList<>();

    public Customer() {
    }

    public Customer(String name, String phoneNumber, String email) {
        this.name = name;
        this.phoneNumber = phoneNumber;
        this.email = email;
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

    public String getPhoneNumber() {
        return phoneNumber;
    }

    public void setPhoneNumber(String phoneNumber) {
        this.phoneNumber = phoneNumber;
    }

    public String getEmail() {
        return email;
    }

    public void setEmail(String email) {
        this.email = email;
    }

    public List<BakeryOrder> getOrders() {
        return orders;
    }

    public void setOrders(List<BakeryOrder> orders) {
        this.orders = orders;
    }
}

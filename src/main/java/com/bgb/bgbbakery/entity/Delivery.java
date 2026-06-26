package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "DELIVERY")
public class Delivery {

    @Id
    @Column(name = "ORDER_ID", precision = 10)
    private Long id;

    @Column(name = "TRACKING_NO", nullable = false, length = 30)
    private String trackingNumber;

    @Column(name = "DATE_TIME", nullable = false)
    private LocalDateTime dateTime;

    @Column(name = "ADDRESS", nullable = false, length = 255)
    private String address;

    @OneToOne
    @MapsId
    @JoinColumn(name = "ORDER_ID")
    private BakeryOrder order;

    public Delivery() {
    }

    public Delivery(String trackingNumber, LocalDateTime dateTime, String address, BakeryOrder order) {
        this.trackingNumber = trackingNumber;
        this.dateTime = dateTime;
        this.address = address;
        this.order = order;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public String getTrackingNumber() {
        return trackingNumber;
    }

    public void setTrackingNumber(String trackingNumber) {
        this.trackingNumber = trackingNumber;
    }

    public LocalDateTime getDateTime() {
        return dateTime;
    }

    public void setDateTime(LocalDateTime dateTime) {
        this.dateTime = dateTime;
    }

    public String getAddress() {
        return address;
    }

    public void setAddress(String address) {
        this.address = address;
    }

    public BakeryOrder getOrder() {
        return order;
    }

    public void setOrder(BakeryOrder order) {
        this.order = order;
    }
}

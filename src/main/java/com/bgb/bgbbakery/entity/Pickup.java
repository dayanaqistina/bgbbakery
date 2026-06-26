package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.time.LocalDateTime;

@Entity
@Table(name = "PICKUP")
public class Pickup {

    @Id
    @Column(name = "ORDER_ID", precision = 10)
    private Long id;

    @Column(name = "DATE_TIME", nullable = false)
    private LocalDateTime dateTime;

    @OneToOne
    @MapsId
    @JoinColumn(name = "ORDER_ID")
    private BakeryOrder order;

    public Pickup() {
    }

    public Pickup(LocalDateTime dateTime, BakeryOrder order) {
        this.dateTime = dateTime;
        this.order = order;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public LocalDateTime getDateTime() {
        return dateTime;
    }

    public void setDateTime(LocalDateTime dateTime) {
        this.dateTime = dateTime;
    }

    public BakeryOrder getOrder() {
        return order;
    }

    public void setOrder(BakeryOrder order) {
        this.order = order;
    }
}

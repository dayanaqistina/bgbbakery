package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.math.BigDecimal;
import java.time.LocalDate;
import java.time.LocalDateTime;

@Entity
@Table(name = "SALESRECEIPT")
public class SalesReceipt {

    @Id
    @GeneratedValue(strategy = GenerationType.IDENTITY)
    @Column(name = "RECEIPT_ID", precision = 10)
    private Long id;

    @Column(name = "RECEIPT_DATE", nullable = false)
    private LocalDate receiptDate;

    @Column(name = "RECEIPT_TIME", nullable = false)
    private LocalDateTime receiptTime;

    @Column(name = "PAYMENT_METHOD", nullable = false, length = 20)
    private String paymentMethod;

    @Column(name = "TOTAL_AMOUNT", nullable = false, precision = 10, scale = 2)
    private BigDecimal totalAmount;

    @OneToOne
    @JoinColumn(name = "ORDER_ID", nullable = false, unique = true)
    private BakeryOrder order;

    public SalesReceipt() {
    }

    public SalesReceipt(LocalDate receiptDate, LocalDateTime receiptTime, String paymentMethod, BigDecimal totalAmount, BakeryOrder order) {
        this.receiptDate = receiptDate;
        this.receiptTime = receiptTime;
        this.paymentMethod = paymentMethod;
        this.totalAmount = totalAmount;
        this.order = order;
    }

    public Long getId() {
        return id;
    }

    public void setId(Long id) {
        this.id = id;
    }

    public LocalDate getReceiptDate() {
        return receiptDate;
    }

    public void setReceiptDate(LocalDate receiptDate) {
        this.receiptDate = receiptDate;
    }

    public LocalDateTime getReceiptTime() {
        return receiptTime;
    }

    public void setReceiptTime(LocalDateTime receiptTime) {
        this.receiptTime = receiptTime;
    }

    public String getPaymentMethod() {
        return paymentMethod;
    }

    public void setPaymentMethod(String paymentMethod) {
        this.paymentMethod = paymentMethod;
    }

    public BigDecimal getTotalAmount() {
        return totalAmount;
    }

    public void setTotalAmount(BigDecimal totalAmount) {
        this.totalAmount = totalAmount;
    }

    public BakeryOrder getOrder() {
        return order;
    }

    public void setOrder(BakeryOrder order) {
        this.order = order;
    }
}

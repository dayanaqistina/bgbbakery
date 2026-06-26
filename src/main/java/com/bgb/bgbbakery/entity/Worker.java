package com.bgb.bgbbakery.entity;

import jakarta.persistence.*;
import java.util.ArrayList;
import java.util.List;

@Entity
@Table(name = "WORKER")
public class Worker {

    @Id
    @Column(name = "WORKER_ID", precision = 10)
    private Long id;

    @Column(name = "WORKER_NAME", nullable = false, length = 50)
    private String name;

    @Column(name = "WORKER_NOPHONE", nullable = false, length = 15)
    private String phoneNumber;

    @ManyToOne
    @JoinColumn(name = "OWNER_ID")
    private Worker owner;

    @OneToMany(mappedBy = "owner")
    private List<Worker> team = new ArrayList<>();

    public Worker() {
    }

    public Worker(String name, String phoneNumber) {
        this.name = name;
        this.phoneNumber = phoneNumber;
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

    public Worker getOwner() {
        return owner;
    }

    public void setOwner(Worker owner) {
        this.owner = owner;
    }

    public List<Worker> getTeam() {
        return team;
    }

    public void setTeam(List<Worker> team) {
        this.team = team;
    }
}

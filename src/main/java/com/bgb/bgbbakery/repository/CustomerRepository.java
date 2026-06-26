package com.bgb.bgbbakery.repository;

import com.bgb.bgbbakery.entity.Customer;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface CustomerRepository extends JpaRepository<Customer, Long> {

    @Query("select coalesce(max(c.id), 0) from Customer c")
    Long findMaxId();
}

package com.bgb.bgbbakery.repository;

import com.bgb.bgbbakery.entity.OrderDetails;
import com.bgb.bgbbakery.entity.OrderDetailsId;
import org.springframework.data.jpa.repository.JpaRepository;

public interface OrderDetailsRepository extends JpaRepository<OrderDetails, OrderDetailsId> {
}

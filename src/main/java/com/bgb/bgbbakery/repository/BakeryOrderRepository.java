package com.bgb.bgbbakery.repository;

import com.bgb.bgbbakery.entity.BakeryOrder;
import org.springframework.data.jpa.repository.JpaRepository;

public interface BakeryOrderRepository extends JpaRepository<BakeryOrder, Long> {
}

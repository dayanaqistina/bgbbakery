package com.bgb.bgbbakery.repository;

import com.bgb.bgbbakery.entity.Worker;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;

public interface WorkerRepository extends JpaRepository<Worker, Long> {

    @Query("select coalesce(max(w.id), 0) from Worker w")
    Long findMaxId();
}

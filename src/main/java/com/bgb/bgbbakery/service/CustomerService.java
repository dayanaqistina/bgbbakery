package com.bgb.bgbbakery.service;

import com.bgb.bgbbakery.entity.Customer;
import com.bgb.bgbbakery.repository.CustomerRepository;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class CustomerService {

    private final CustomerRepository customerRepository;

    public CustomerService(CustomerRepository customerRepository) {
        this.customerRepository = customerRepository;
    }

    public List<Customer> findAll() {
        return customerRepository.findAll();
    }

    public Customer findById(Long id) {
        return customerRepository.findById(id).orElse(null);
    }

    public Customer save(Customer customer) {
        if (customer.getId() == null) {
            customer.setId(customerRepository.findMaxId() + 1);
        }
        if (customer.getEmail() == null || customer.getEmail().isBlank()) {
            customer.setEmail("not-provided@bgb.local");
        }
        return customerRepository.save(customer);
    }

    public void deleteById(Long id) {
        customerRepository.deleteById(id);
    }
}

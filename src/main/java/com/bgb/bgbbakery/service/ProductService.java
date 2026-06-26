package com.bgb.bgbbakery.service;

import com.bgb.bgbbakery.entity.Product;
import com.bgb.bgbbakery.repository.ProductRepository;
import org.springframework.stereotype.Service;

import java.util.List;

@Service
public class ProductService {

    private final ProductRepository productRepository;

    public ProductService(ProductRepository productRepository) {
        this.productRepository = productRepository;
    }

    public List<Product> findAll() {
        return productRepository.findAll();
    }

    public Product findById(Long id) {
        return productRepository.findById(id).orElse(null);
    }

    public Product save(Product product) {
        if (product.getDescription() == null || product.getDescription().isBlank()) {
            product.setDescription(product.getFlavourTopping() == null || product.getFlavourTopping().isBlank()
                    ? "Freshly baked to order."
                    : product.getFlavourTopping());
        }
        return productRepository.save(product);
    }

    public void deleteById(Long id) {
        productRepository.deleteById(id);
    }
}

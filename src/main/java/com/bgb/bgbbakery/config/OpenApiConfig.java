package com.bgb.bgbbakery.config;

import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Info;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenApiConfig {

    @Bean
    public OpenAPI bakeryOpenAPI() {
        return new OpenAPI()
                .info(new Info()
                        .title("BGB Bakehouse API")
                        .description("REST API for bakery order and product management")
                        .version("v1.0"));
    }
}

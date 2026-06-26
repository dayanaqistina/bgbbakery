# Stage 1: Build the application
FROM eclipse-temurin:21-jdk-jammy AS build

WORKDIR /app

# Copy maven executable to the image
COPY mvnw .
COPY .mvn .mvn
COPY pom.xml .

# Ensure mvnw has execution permission
RUN chmod +x ./mvnw

# Download all required dependencies into one layer
RUN ./mvnw dependency:go-offline -B

# Copy your other files
COPY src src

# Build project
RUN ./mvnw package -DskipTests

# Stage 2: Run the application
FROM eclipse-temurin:21-jre-jammy

WORKDIR /app

# Copy over the built artifact from the maven image
COPY --from=build /app/target/*.jar app.jar

# Expose port 8080
EXPOSE 8080

# Run the jar file 
ENTRYPOINT ["java", "-jar", "app.jar"]

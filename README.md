# Inventory Management System - DBMS Project

## Overview
This project is a simple **Inventory Management System** designed to manage product stock, sales, and supplier details using a relational database. It is implemented using **SQL** and demonstrates core DBMS concepts such as table creation, insertion, querying, and basic CRUD (Create, Read, Update, Delete) operations. The system is intended for small businesses to track inventory levels and manage transactions efficiently.
![image](https://github.com/user-attachments/assets/bc67f006-0bfe-46c2-a9fa-38e9c49eaffe)

## Features
- Add new products to the inventory.
- Update stock levels based on sales or restocking.
- View current inventory details.
- Manage supplier information.
- Generate basic reports (e.g., low stock alerts).
- Perform CRUD operations on the database.

## Technologies Used
- **Database**: MySQL (or any SQL-compliant DBMS like PostgreSQL).
- **Language**: SQL for queries and schema definition.
- **Tools**: Any SQL client (e.g., MySQL Workbench, DBeaver, or command-line interface).
![image](https://github.com/user-attachments/assets/6f992982-5586-4aeb-b320-f752d4c5435b)

## Project Structure
- `schema.sql`: Contains the SQL script to create the database and tables.
- `data.sql`: Contains sample data to populate the tables.
- `queries.sql`: Includes example CRUD operations and reports.
- `README.md`: This file, providing project documentation.
![image](https://github.com/user-attachments/assets/a992298a-fa1d-4a0a-95b1-eab518641147)

## Installation and Setup
1. **Install a DBMS**:
   - Install MySQL or any SQL-compliant DBMS on your system.
   - Ensure the SQL client is configured to connect to your database server.

2. **Create the Database**:
   - Open your SQL client and run the following command to create a new database:
     ```sql
     CREATE DATABASE inventory_db;
     USE inventory_db;

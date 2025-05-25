Overview

This project is a Database Management System (DBMS) designed for a Non-Governmental Organization (NGO) to efficiently manage its operations. The system handles data related to donors, beneficiaries, funds, and events, ensuring smooth tracking and reporting of activities. The project is implemented using SQL for database creation and management, with additional scripts for data insertion, querying, and reporting.
![image](https://github.com/user-attachments/assets/bc67f006-0bfe-46c2-a9fa-38e9c49eaffe)

Key Features
Donors Management: Store and manage donor details (name, contact, donation amount, etc.).
Beneficiaries Management: Track beneficiaries receiving aid (name, aid type, status, etc.).
Funds Tracking: Record financial transactions, including donations and expenditures.
Events Management: Organize and monitor NGO events (event name, date, location, etc.).
Reporting: Generate reports for donations, fund allocation, and event summaries.
Data Integrity: Ensure data consistency using primary and foreign keys, constraints, and triggers.

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

Setup Instructions
Install MySQL:
Download and install MySQL Community Server from MySQL Official Website.
Install MySQL Workbench for easier database management.
Create the Database:
Open MySQL Workbench or command-line interface.
Run the following command to create the database:

CREATE DATABASE project;
USE project;

Create Tables:
Execute the SQL script create_tables.sql (available in the sql_scripts/ folder) to create the necessary tables.

-- Example table creation for Donors
CREATE TABLE Donors (
    donor_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    contact VARCHAR(15),
    email VARCHAR(100),
    total_donated DECIMAL(10, 2) DEFAULT 0.0
);

Repeat for other tables (Beneficiaries, Funds, Events).
Insert Sample Data:
Use the insert_data.sql script to populate the tables with sample data.
INSERT INTO Donors (name, contact, email, total_donated) 
VALUES ('sibghat', '03001234567', 'sib@email.com', 5000.00);

Run Queries:
Use the queries.sql script to run predefined queries for reporting and data retrieval.
Usage
Add New Donors/Beneficiaries: Use INSERT queries to add new records.
INSERT INTO Beneficiaries (name, aid_type, status) 
VALUES ('Ahmed Raza', 'Education', 'Active');
Track Funds: Record donations and expenditures in the Funds table.
INSERT INTO Funds (donor_id, amount, date, purpose) 
VALUES (1, 2000.00, '2025-05-25', 'Education Program');

Generate Reports:
Total donations by a donor:
SELECT name, total_donated FROM Donors WHERE donor_id = 1;

Upcoming events:
SELECT name, date, location FROM Events WHERE date >= CURDATE();

Best Practices
Backup Regularly: Use MySQL's export feature to back up the database.
Indexing: Add indexes on frequently queried columns (e.g., donor_id, event_date) for faster retrieval.
Data Validation: Use constraints (NOT NULL, CHECK) to ensure data integrity.
Error Handling: Implement error handling in application code connecting to the database.

Future Enhancements
Add a frontend interface (e.g., using Python Flask or Java Swing) for easier interaction.
Implement user authentication for secure access.
Add analytics for better fund allocation and event planning.
Contact
For any queries or contributions, contact:
Name: sibghatullah
Email: sibghatullah1a2a3a@gmail.com

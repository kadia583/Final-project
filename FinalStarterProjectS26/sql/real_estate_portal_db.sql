-- =========================================================
-- Real Estate Agency Portal Starter SQL
-- Spring 2026
-- =========================================================

CREATE DATABASE IF NOT EXISTS real_estate_portal_db;
USE real_estate_portal_db;

DROP TABLE IF EXISTS Favorites;
DROP TABLE IF EXISTS Transactions;
DROP TABLE IF EXISTS Inquiries;
DROP TABLE IF EXISTS Properties;
DROP TABLE IF EXISTS Users;

CREATE TABLE Users (
    userId INT NOT NULL AUTO_INCREMENT,
    userName VARCHAR(50) NOT NULL UNIQUE,
    contactInfo VARCHAR(200),
    passwordHash VARCHAR(255) NOT NULL,
    userType ENUM('agent', 'buyer', 'renter') NOT NULL,
    PRIMARY KEY (userId)
);
CREATE TABLE Properties (
    propertyId INT NOT NULL UNIQUE AUTO_INCREMENT,
    title VARCHAR(100) NOT NULL,
    propertyType VARCHAR(50) NOT NULL,
	address VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    status ENUM('available', 'sold', 'rented') NOT NULL DEFAULT 'available',
	agentId INT NOT NULL,
    Primary Key (propertyId),
    Foreign Key (agentId) references Users(userId)
);
CREATE TABLE Inquiries (
    inquiryId INT NOT NULL UNIQUE AUTO_INCREMENT,
    userId INT NOT NULL,
    propertyId INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    inquiryDate DATETIME NOT NULL,
    Primary Key (inquiryId),
    Foreign Key (userId) references Users(userId),
    Foreign Key (propertyId) references Properties(propertyId)
);
CREATE TABLE Transactions(
    transactionId INT NOT NULL UNIQUE AUTO_INCREMENT,
	propertyId INT NOT NULL,
    userId INT NOT NULL,
    transactionType ENUM('sale', 'rental') NOT NULL,
    transactionDate DATETIME NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    Primary Key (transactionId),
    Foreign Key (propertyId) references Properties(propertyId),
    Foreign Key (userId) references Users(userId)
);
CREATE TABLE Favorites(
     favoriteId INT NOT NULL UNIQUE AUTO_INCREMENT,
     userId INT NOT NULL,
     propertyId INT NOT NULL,
     savedDate DATETIME NOT NULL,
     Primary Key (favoriteId),
     Foreign Key (userId) references Users(userId),
     Foreign Key (propertyId) references Properties(propertyId)
);



-- ---------------------------------------------------------
-- Sample Data
-- NOTE: Password hashes below are placeholders.
-- ---------------------------------------------------------
INSERT INTO Users (userName, contactInfo, passwordHash, userType) VALUES
('agent_maria', 'maria@agency.com', '$2y$10$examplehash0000000000000000000000000000000000000', 'agent'),
('buyer_james', 'james@email.com', '$2y$10$examplehash0000000000000000000000000000000000001', 'buyer'),
('renter_lisa', 'lisa@email.com', '$2y$10$examplehash0000000000000000000000000000000000002', 'renter');

INSERT INTO Properties (title, propertyType, address, city, price, agentId) VALUES
 ('Cozy Studio Loft', 'Studio', '789 Pine St, #2A', 'Brooklyn', 450000.00, 101),
('Luxury Waterfront Villa', 'House', '101 Bay View Dr', 'Miami', 3500000.00, 103),
('Office Space Downtown', 'Commercial', '202 Business Hub', 'New York', 2100000.00, 102);

INSERT INTO Inquiries (userId, propertyId, message, inquiryDate) VALUES
(101, 5005, 'Is this property still available?', '2026-05-04'),
(102, 5006, 'This property is available', '2026-05-05'),
(103, 5007, 'Is this property still on the market?', '2026-05-06');

INSERT INTO Transactions (propertyId, userId, transactionType, transactionDate, amount) VALUES
(101, 5001, 'Purchase', '2026-05-04', 250000.00),
(101, 5001, 'Buy', '2026-05-04', 300000.00),
(101, 5001, 'Obtain', '2026-05-04', 350000.00);

INSERT INTO Favorites(userId, propertyId, savedDate)VALUES 
(1, 102, '2026-05-04 10:00:00'),
(2, 103, '2026-05-04 10:30:00'),
(3, 104, '2026-05-04 10:40:00');


DELIMITER $$

CREATE PROCEDURE AddOrUpdateUser(
    IN uid INT,
    IN uname VARCHAR(50),
    IN contact VARCHAR(200),
    IN passHash VARCHAR(255),
    IN utype ENUM('agent','buyer','renter')
)
BEGIN
    IF uid IS NULL THEN
        INSERT INTO Users(userName, contactInfo, passwordHash, userType)
        VALUES (uname, contact, passHash, utype);
    ELSE
        UPDATE Users
        SET userName = uname,
            contactInfo = contact,
            passwordHash = passHash,
            userType = utype
        WHERE userId = uid;
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE ProcessTransaction(
    IN propId INT,
    IN uId INT,
    IN tType ENUM('sale','rental'),
    IN amt DECIMAL(12,2)
)
BEGIN
    INSERT INTO Transactions(propertyId, userId, transactionType, transactionDate, amount)
    VALUES (propId, uId, tType, NOW(), amt);

    IF tType = 'sale' THEN
        UPDATE Properties SET status = 'sold' WHERE propertyId = propId;
    ELSE
        UPDATE Properties SET status = 'rented' WHERE propertyId = propId;
        END IF;
END $$

DELIMITER ;
CREATE VIEW PropertyListingView AS
SELECT 
    p.propertyId,
    p.title,
    p.propertyType,
    p.city,
    p.address,
    p.price,
    p.status,
    u.userName AS agentName
FROM Properties p
JOIN Users u ON p.agentId = u.userId;
DELIMITER $$

CREATE TRIGGER AfterTransactionInsert
AFTER INSERT ON Transactions
FOR EACH ROW
BEGIN
    IF NEW.transactionType = 'sale' THEN
        UPDATE Properties SET status = 'sold' WHERE propertyId = NEW.propertyId;
    ELSE
        UPDATE Properties SET status = 'rented' WHERE propertyId = NEW.propertyId;
    END IF;
END $$

DELIMITER ;

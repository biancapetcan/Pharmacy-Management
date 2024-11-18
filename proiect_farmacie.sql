CREATE TABLE Medicamente (
ID_Medicament INT AUTO_INCREMENT NOT NULL,
Nume_Medicament nvarchar(50) NOT NULL,
Tip_Medicament nvarchar(50) NOT NULL,
Pret_Medicament decimal(10,2),
CONSTRAINT PK_ID_Medicament PRIMARY KEY(ID_Medicament),
CONSTRAINT UNQ_Nume_Medicament UNIQUE(Nume_Medicament)
);

CREATE TABLE Furnizori(
ID_Furnizor INT NOT NULL AUTO_INCREMENT,
ID_Medicament INT NOT NULL,
Nume_Furnizor nvarchar(50) NOT NULL,
Telefon varchar(10),
CONSTRAINT PK_ID_Furnizor PRIMARY KEY(ID_Furnizor),
CONSTRAINT FK_ID_Medicament_Furnizori FOREIGN KEY(ID_Medicament) REFERENCES Medicamente(ID_Medicament)
);

CREATE TABLE Medicamente_Furnizori(
ID_Medicament INT NOT NULL,
ID_Furnizor INT NOT NULL,
CONSTRAINT PK_Medicament_Furnizor PRIMARY KEY(ID_Medicament,ID_Furnizor)
);

CREATE TABLE Furnizori_Comenzi(
ID_Furnizor INT NOT NULL,
ID_Comanda INT NOT NULL,
CONSTRAINT PK_ID_Furnizor_ID_Comanda PRIMARY KEY(ID_furnizor, ID_Comanda)
);

CREATE TABLE Comenzi(
ID_Comanda INT NOT NULL auto_increment,
ID_Furnizor INT NOT NULL,
ID_Medicament INT NOT NULL,
Data_Comanda date,
Total_Comanda decimal(10,2),
CONSTRAINT PK_ID_Comanda PRIMARY KEY(ID_Comanda),
CONSTRAINT FK_ID_Furnizor_Comenzi FOREIGN KEY(ID_Furnizor) REFERENCES Furnizori(ID_Furnizor)
);

CREATE TABLE Medicamente_Comenzi(
ID_Medicament INT NOT NULL,
ID_Comanda INT NOT NULL,
CONSTRAINT PK_Medicamente_Comenzi PRIMARY KEY(ID_Medicament, ID_Comanda)
);

CREATE TABLE Stocuri(
ID_Stoc INT NOT NULL auto_increment,
ID_Medicament INT NOT NULL,
Cantitate INT NOT NULL,
CONSTRAINT PF_Stoc PRIMARY KEY(ID_Stoc),
CONSTRAINT FK_ID_Medicament_Stoc FOREIGN KEY(ID_Medicament) REFERENCES Medicamente(ID_Medicament)
);

CREATE TABLE Cumparari (
ID_Cumparare INT NOT NULL auto_increment,
ID_Medicament INT NOT NULL,
Cantitate INT,
Data_Vanzare decimal(10,2),
CONSTRAINT PK_ID_Cumaprare PRIMARY KEY(ID_Cumparare),
CONSTRAINT FK_ID_Medicament_Cumparari FOREIGN KEY(ID_Medicament) REFERENCES Medicamente(ID_Medicament)
);

INSERT INTO Medicamente (ID_Medicament, Nume_Medicament, Tip_Medicament, Pret_Medicament) VALUES
(1, 'Magneziu', 'Supliment', 13.00),
(2, 'Ibuprofen', 'Analgezic', 15.50),
(3, 'Amoxicilina', 'Antibiotic', 20.00),
(4, 'Vitamina C', 'Supliment', 7.25),
(5, 'Aspirina', 'Antiinflamator', 4.50),
(6, 'Nurofen', 'Antiinflamator', 36.00);

INSERT INTO Furnizori (ID_Furnizor, ID_Medicament, Nume_Furnizor, Telefon) VALUES
(100, 1, 'Pharma', '0712345678'),
(101, 2, 'BioFarm', '0722345678'),
(102, 3, 'MedExpress', '0732345678'),
(103, 4, 'Farmex', '0742345678'),
(104, 5, 'MedPro', '0752345678'),
(105, 6, 'Convalaria', '0755578402');

INSERT INTO Medicamente_Furnizori (ID_Medicament, ID_Furnizor) VALUES
(1, 100),
(2, 101),
(3, 102),
(4, 103),
(5, 104),
(6, 105);

INSERT INTO Furnizori_Comenzi (ID_Furnizor, ID_Comanda) VALUES
(100, 1001),
(101, 1002),
(102, 1003),
(103, 1004),
(104, 1005),
(105, 1006);

INSERT INTO Comenzi (ID_Comanda, ID_Furnizor, ID_Medicament, Data_Comanda, Total_Comanda) VALUES
(1001, 100, 1, '2024-01-10', 252.00),
(1002, 101, 2, '2024-02-15', 528.50),
(1003, 102, 3, '2024-03-20', 1000.00),
(1004, 103, 4, '2024-04-25', 369.25),
(1005, 104, 5, '2024-05-30', 220.50),
(1006, 105, 6, '2024-07-30', 257.00);

INSERT INTO Medicamente_Comenzi (ID_Medicament, ID_Comanda) VALUES
(1, 1001),
(2, 1002),
(3, 1003),
(4, 1004),
(5, 1005),
(6, 1006);

INSERT INTO Stocuri (ID_Stoc, ID_Medicament, Cantitate) VALUES
(110, 1, 150),
(111, 2, 200),
(112, 3, 500),
(113, 4, 360),
(114, 5, 250),
(115, 6, 367);

INSERT INTO Cumparari (ID_Cumparare, ID_Medicament, Cantitate, Data_Vanzare) VALUES
(10, 1, 70, '2024-01-15'),
(11, 2, 100, '2024-02-18'),
(12, 3, 130, '2024-03-22'),
(13, 4, 150, '2024-04-27'),
(14, 5, 90, '2024-05-31'),
(15, 6, 110, '2024-06-25');

CREATE TABLE USERS(
NUME varchar(50) NOT NULL,
PRENUME VARCHAR(50) NOT NULL,
EMAIL VARCHAR(60) NOT NULL,
PAROLA VARCHAR(40) NOT NULL,
ROL ENUM('Administrator Farmacie','Furnizor') NOT NULL DEFAULT 'Furnizor'
);

INSERT INTO USERS (NUME, PRENUME, EMAIL, PAROLA, ROL)
VALUES 
('Popescu', 'Andrei', 'andreiandrei@yahoo.com', 'andrei1009', 'Administrator Farmacie'),
('Negrescu', 'Denis', 'sined21@gmail.com', 'sined2104', 'Furnizor'),
('Georgescu', 'Vlad', 'vlad.georgescu@gmail.com', 'parola123', 'Furnizor'),
('Dumitrescu', 'Elena', 'elena.dumitrescu@yahoo.com', 'elena2023', 'Administrator Farmacie'),
('Radu', 'Mihai', 'mihai.radu@gmail.com', 'raduandrei17', 'Furnizor'),
('Ionescu', 'Bogdan', 'bogdan_i@yahoo.com', 'bogdanionescu40', 'Administrator Farmacie');

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    ic VARCHAR(20),
    organization VARCHAR(255),
    role ENUM('admin', 'volunteer') NOT NULL DEFAULT 'volunteer',
    status ENUM('active', 'suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Asnaf table
CREATE TABLE asnaf (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    ic VARCHAR(20) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    kampung VARCHAR(100) NOT NULL,
    tl VARCHAR(100),
    occupation VARCHAR(100),
    status ENUM('pending', 'verified', 'assisted') NOT NULL DEFAULT 'pending',
    total_dependent INT DEFAULT 0,
    dependent_names TEXT,
    problems TEXT,
    picture VARCHAR(255),
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Kampung Groups table (new)
CREATE TABLE kampung_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,  -- e.g. "Kampung A", "Kampung B"
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Volunteer Assignments (new)
CREATE TABLE volunteer_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    volunteer_id INT NOT NULL,
    kampung_id INT NOT NULL,
    is_leader BOOLEAN DEFAULT FALSE,
    assigned_by INT NOT NULL,  -- Admin who made this assignment
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (volunteer_id) REFERENCES users(id),
    FOREIGN KEY (kampung_id) REFERENCES kampung_groups(id),
    FOREIGN KEY (assigned_by) REFERENCES users(id),
    UNIQUE KEY (volunteer_id, kampung_id)  -- A volunteer can only be assigned once per kampung
);

-- Tasks table (new)
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    kampung_id INT NOT NULL,
    assigned_to INT,  -- Specific volunteer (optional)
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    due_date DATE,
    created_by INT NOT NULL,  -- Admin who created the task
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kampung_id) REFERENCES kampung_groups(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

INSERT INTO `asnaf` (`id`, `name`, `ic`, `phone`, `address`, `kampung`, `tl`, `occupation`, `status`, `total_dependent`, `dependent_names`, `problems`, `picture`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Encik Ahmad', '801010-05-5555', '0171112233', 'Lot 10, Jalan Parit Raja, Batu Pahat', 'Kg Parit Haji Ali', 'Tok Imam Ali', 'Petani', 'verified', 4, 'Ali, Fatimah, Zainab, Abu', 'Tidak mampu bayar bil elektrik', 'ahmad.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(2, 'Puan Aminah', '820202-06-6666', '0182223344', 'No. 2, Lorong Kampung Baru, Batu Pahat', 'Kg Baru', 'Ketua Kampung Hasan', 'Surirumah', 'pending', 3, 'Nina, Anis, Farah', 'Sakit kronik, tiada pekerjaan', 'aminah.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(3, 'Encik Rahman', '830303-07-7777', '0193334455', 'Jalan Pahlawan 3, Taman Universiti', 'Kg Sri Gading', 'Pak Mail', 'Pekerja am', 'assisted', 2, 'Syafiq, Iqbal', 'Rumah rosak akibat banjir', 'rahman.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(4, 'Cik Siti Nor', '850505-08-8888', '0164445566', 'Jalan Mawar 4, Parit Raja', 'Kg Seri Medan', 'Ustazah Halimah', 'Tiada pekerjaan', 'pending', 5, 'Amir, Sarah, Nabil, Yusof, Najwa', 'Anak ramai, tiada pendapatan tetap', 'siti.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(5, 'Encik Zulkifli', '860606-09-9999', '0175556677', 'No. 5, Jalan Dahlia, Sri Gading', 'Kg Bukit Pasir', 'Encik Karim', 'Penoreh getah', 'verified', 3, 'Afiq, Aina, Hana', 'Pendapatan tidak menentu, sakit buah pinggang', 'zulkifli.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(6, 'Puan Rohani', '870707-10-1010', '0186667788', 'Rumah Flat Blok B, Taman Soga', 'Kg Pt. Tengah', 'Pn. Azizah', 'Pembantu kedai', 'assisted', 1, 'Solehah', 'Ibu tunggal, gaji minimum', 'rohani.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(7, 'Encik Faizal', '880808-11-1111', '0197778899', 'No. 8, Jalan Intan, Parit Sulong', 'Kg Parit Raja Darat', 'Tok Batin Mamat', 'Buruh binaan', 'pending', 2, 'Danial, Dina', 'Rumah hampir roboh, tidak mampu baiki', 'faizal.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(8, 'Puan Zainab', '890909-12-1212', '0138889900', 'Kampung Sri Wangi, Bt. Pahat', 'Kg Sri Wangi', 'Ketua Kampung Farid', 'Penjaja kecil', 'verified', 4, 'Hafiz, Iman, Laila, Aziz', 'Tiada gerai tetap, pendapatan tidak stabil', 'zainab.jpg', 2, '2025-06-09 16:52:46', '2025-06-09 16:52:46'),
(9, 'vimal', '760101-11-5555', '0109412968', 'No.347,Lor 5, Jln Kuching Serian,Batu 17', NULL, 'Zone A', 'Penjaja kecil', 'pending', 2, '21112', '21111', 'uploads/asnaf/68482ea847188.jpg', 2, '2025-06-10 13:10:00', '2025-06-10 13:10:00');

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `phone`, `ic`, `organization`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin1', 'admin@example.com', 'admin123hash', '0123456789', '900101-01-1234', 'One Heart Team', 'admin', 'active', '2025-06-09 16:50:26', '2025-06-09 16:50:26'),
(2, 'Volunteer A', 'vola', 'vola@example.com', 'vola123hash', '0112233445', '920202-02-4567', 'One Heart Team', 'volunteer', 'active', '2025-06-09 16:50:26', '2025-06-10 12:55:50'),
(3, 'Volunteer B', 'volb', 'volb@example.com', 'volb123hash', '0123344556', '930303-03-5678', 'One Heart Team', 'volunteer', 'active', '2025-06-09 16:50:26', '2025-06-09 16:50:26'),
(4, 'Volunteer C', 'volc', 'volc@example.com', 'volc123hash', '0134455667', '940404-04-6789', 'One Heart Team', 'volunteer', 'active', '2025-06-09 16:50:26', '2025-06-10 12:57:21');
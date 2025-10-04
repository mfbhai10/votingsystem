-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2023 at 03:18 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `votingsystem`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `firstname`, `lastname`, `photo`, `created_on`) VALUES
(1, 'admin', '$2y$10$eoDZ8wGMOvMB/l/jF8UKEeBv2Co97I5CqmkIu.sUQxisnpqVFZ8wm', 'Admin', 'Admin', 'facebook-profile-image.jpeg', '2025-09-02');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(150) NOT NULL,
  `platform` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `description` varchar(50) NOT NULL,
  `max_vote` int(11) NOT NULL,
  `priority` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `voters`
--

CREATE TABLE `voters` (
  `id` int(11) NOT NULL,
  `voters_id` varchar(15) NOT NULL,
  `password` varchar(60) NOT NULL,
  `firstname` varchar(30) NOT NULL,
  `lastname` varchar(30) NOT NULL,
  `photo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `votes`
--

CREATE TABLE `votes` (
  `id` int(11) NOT NULL,
  `voters_id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `position_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `voters`
--
ALTER TABLE `voters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `votes`
--
ALTER TABLE `votes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `voters`
--
ALTER TABLE `voters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `votes`
--
ALTER TABLE `votes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- 1.1 Elections table
CREATE TABLE IF NOT EXISTS elections (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description TEXT,
  starts_at DATETIME NOT NULL,
  ends_at   DATETIME NOT NULL,
  status ENUM('draft','scheduled','open','closed','archived') NOT NULL DEFAULT 'draft',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 1.2 Scope positions to election
ALTER TABLE positions
  ADD COLUMN election_id INT NOT NULL DEFAULT 0 AFTER id;

ALTER TABLE positions
  ADD CONSTRAINT fk_positions_election
  FOREIGN KEY (election_id) REFERENCES elections(id)
  ON DELETE CASCADE;

-- 1.3 Scope candidates to election (simple + safe)
ALTER TABLE candidates
  ADD COLUMN election_id INT NOT NULL DEFAULT 0 AFTER id;

ALTER TABLE candidates
  ADD CONSTRAINT fk_candidates_election
  FOREIGN KEY (election_id) REFERENCES elections(id)
  ON DELETE CASCADE;

-- 1.4 Scope votes to election
ALTER TABLE votes
  ADD COLUMN election_id INT NOT NULL DEFAULT 0 AFTER id,
  ADD COLUMN created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE votes
  ADD CONSTRAINT fk_votes_election
  FOREIGN KEY (election_id) REFERENCES elections(id)
  ON DELETE CASCADE;

ALTER TABLE votes
  ADD CONSTRAINT fk_votes_position
  FOREIGN KEY (position_id) REFERENCES positions(id)
  ON DELETE CASCADE;

ALTER TABLE votes
  ADD CONSTRAINT fk_votes_candidate
  FOREIGN KEY (candidate_id) REFERENCES candidates(id)
  ON DELETE CASCADE;

-- 1.5 Optional integrity: prevent duplicate same-candidate vote per position/election/voter
CREATE UNIQUE INDEX IF NOT EXISTS ux_votes_once_per_candidate
  ON votes (election_id, position_id, voters_id, candidate_id);

-- 1.6 Backfill: create a single election row for current legacy data
INSERT INTO elections (title, description, starts_at, ends_at, status)
VALUES ('Legacy Election', 'Imported from single-election system', NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'open');

-- capture id
SET @legacy_id := LAST_INSERT_ID();

-- set election_id on existing rows
UPDATE positions  SET election_id = @legacy_id WHERE election_id = 0;
UPDATE candidates SET election_id = @legacy_id WHERE election_id = 0;
UPDATE votes      SET election_id = @legacy_id WHERE election_id = 0;

-- 1.7 Helpful uniqueness
ALTER TABLE voters ADD UNIQUE KEY uq_voters_voters_id (voters_id);

ALTER TABLE voters
  ADD COLUMN election_id INT NOT NULL DEFAULT 0 AFTER id,
  ADD CONSTRAINT fk_voters_election
  FOREIGN KEY (election_id) REFERENCES elections(id)
  ON DELETE CASCADE;

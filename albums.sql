SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `albums`
--

-- --------------------------------------------------------

--
-- Table structure for table `albummodel`
--

CREATE TABLE `albummodel` (
                              `Id` int(11) NOT NULL AUTO_INCREMENT,
                              `Title` varchar(50) NOT NULL,
                              `Description` text NOT NULL,
                              `CreatedBy` int(11) NOT NULL,
                              `CreatedAt` datetime NOT NULL,
                              PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `commentmodel`
--

CREATE TABLE `commentmodel` (
                                `Id` int(11) NOT NULL AUTO_INCREMENT,
                                `Comment` text NOT NULL,
                                `StatusId` int(11) NOT NULL,
                                `CreatedAt` datetime NOT NULL,
                                `WhoCommented` int(11) NOT NULL,
                                PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `likemodel`
--

CREATE TABLE `likemodel` (
                             `Id` int(11) NOT NULL AUTO_INCREMENT,
                             `StatusId` int(11) NOT NULL,
                             `WhoLiked` int(11) NOT NULL,
                             `CreatedAt` datetime NOT NULL,
                             PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `photomodel`
--

CREATE TABLE `photomodel` (
                              `Id` int(11) NOT NULL AUTO_INCREMENT,
                              `Title` varchar(50) NOT NULL,
                              `Description` text NOT NULL,
                              `Path` text NOT NULL,
                              `UploadedBy` int(11) NOT NULL,
                              `AlbumId` int(11) NOT NULL,
                              `CreatedAt` datetime NOT NULL,
                              PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `statusmodel`
--

CREATE TABLE `statusmodel` (
                               `Id` int(11) NOT NULL AUTO_INCREMENT,
                               `Status` text NOT NULL,
                               `NumberOfLikes` int(11) NOT NULL,
                               `PhotoId` int(11) NOT NULL,
                               `CreatedAt` datetime NOT NULL,
                               `UserId` int(11) NOT NULL,
                               PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `usermodel`
--

CREATE TABLE `usermodel` (
                             `Id` int(11) NOT NULL AUTO_INCREMENT,
                             `FirstName` varchar(50) NOT NULL,
                             `LastName` varchar(50) NOT NULL,
                             `Email` varchar(100) NOT NULL,
                             `Password` varchar(100) NOT NULL,
                             `CreatedAt` datetime NOT NULL,
                             PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

ALTER TABLE statusmodel ADD COLUMN NumberOfDislikes INT DEFAULT 0;
ALTER TABLE likemodel ADD COLUMN WhoDisliked INT;
ALTER TABLE likemodel MODIFY COLUMN WhoLiked INT DEFAULT 1;
ALTER TABLE statusmodel ADD COLUMN NumberOfComments INT DEFAULT 0;
ALTER TABLE statusmodel MODIFY COLUMN CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE statusmodel MODIFY COLUMN CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP;


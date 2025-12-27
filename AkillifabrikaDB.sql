USE [master]
GO
/****** Object:  Database [AkilliFabrikaDB]    Script Date: 21.12.2025 18:45:55 ******/
CREATE DATABASE [AkilliFabrikaDB]
 CONTAINMENT = NONE
 ON  PRIMARY 
( NAME = N'AkilliFabrikaDB', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SQLEXPRESS01\MSSQL\DATA\AkilliFabrikaDB.mdf' , SIZE = 8192KB , MAXSIZE = UNLIMITED, FILEGROWTH = 65536KB )
 LOG ON 
( NAME = N'AkilliFabrikaDB_log', FILENAME = N'C:\Program Files\Microsoft SQL Server\MSSQL16.SQLEXPRESS01\MSSQL\DATA\AkilliFabrikaDB_log.ldf' , SIZE = 8192KB , MAXSIZE = 2048GB , FILEGROWTH = 65536KB )
 WITH CATALOG_COLLATION = DATABASE_DEFAULT, LEDGER = OFF
GO
ALTER DATABASE [AkilliFabrikaDB] SET COMPATIBILITY_LEVEL = 160
GO
IF (1 = FULLTEXTSERVICEPROPERTY('IsFullTextInstalled'))
begin
EXEC [AkilliFabrikaDB].[dbo].[sp_fulltext_database] @action = 'enable'
end
GO
ALTER DATABASE [AkilliFabrikaDB] SET ANSI_NULL_DEFAULT OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ANSI_NULLS OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ANSI_PADDING OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ANSI_WARNINGS OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ARITHABORT OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET AUTO_CLOSE ON 
GO
ALTER DATABASE [AkilliFabrikaDB] SET AUTO_SHRINK OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET AUTO_UPDATE_STATISTICS ON 
GO
ALTER DATABASE [AkilliFabrikaDB] SET CURSOR_CLOSE_ON_COMMIT OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET CURSOR_DEFAULT  GLOBAL 
GO
ALTER DATABASE [AkilliFabrikaDB] SET CONCAT_NULL_YIELDS_NULL OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET NUMERIC_ROUNDABORT OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET QUOTED_IDENTIFIER OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET RECURSIVE_TRIGGERS OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET  ENABLE_BROKER 
GO
ALTER DATABASE [AkilliFabrikaDB] SET AUTO_UPDATE_STATISTICS_ASYNC OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET DATE_CORRELATION_OPTIMIZATION OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET TRUSTWORTHY OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ALLOW_SNAPSHOT_ISOLATION OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET PARAMETERIZATION SIMPLE 
GO
ALTER DATABASE [AkilliFabrikaDB] SET READ_COMMITTED_SNAPSHOT OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET HONOR_BROKER_PRIORITY OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET RECOVERY SIMPLE 
GO
ALTER DATABASE [AkilliFabrikaDB] SET  MULTI_USER 
GO
ALTER DATABASE [AkilliFabrikaDB] SET PAGE_VERIFY CHECKSUM  
GO
ALTER DATABASE [AkilliFabrikaDB] SET DB_CHAINING OFF 
GO
ALTER DATABASE [AkilliFabrikaDB] SET FILESTREAM( NON_TRANSACTED_ACCESS = OFF ) 
GO
ALTER DATABASE [AkilliFabrikaDB] SET TARGET_RECOVERY_TIME = 60 SECONDS 
GO
ALTER DATABASE [AkilliFabrikaDB] SET DELAYED_DURABILITY = DISABLED 
GO
ALTER DATABASE [AkilliFabrikaDB] SET ACCELERATED_DATABASE_RECOVERY = OFF  
GO
ALTER DATABASE [AkilliFabrikaDB] SET QUERY_STORE = ON
GO
ALTER DATABASE [AkilliFabrikaDB] SET QUERY_STORE (OPERATION_MODE = READ_WRITE, CLEANUP_POLICY = (STALE_QUERY_THRESHOLD_DAYS = 30), DATA_FLUSH_INTERVAL_SECONDS = 900, INTERVAL_LENGTH_MINUTES = 60, MAX_STORAGE_SIZE_MB = 1000, QUERY_CAPTURE_MODE = AUTO, SIZE_BASED_CLEANUP_MODE = AUTO, MAX_PLANS_PER_QUERY = 200, WAIT_STATS_CAPTURE_MODE = ON)
GO
USE [AkilliFabrikaDB]
GO
/****** Object:  User [webuser]    Script Date: 21.12.2025 18:45:55 ******/
CREATE USER [webuser] FOR LOGIN [webuser] WITH DEFAULT_SCHEMA=[dbo]
GO
ALTER ROLE [db_owner] ADD MEMBER [webuser]
GO
/****** Object:  Table [dbo].[Arizalar]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Arizalar](
	[ArizaID] [int] IDENTITY(1,1) NOT NULL,
	[MakineID] [int] NOT NULL,
	[ArizaKodu] [nvarchar](20) NULL,
	[ArizaTanimi] [nvarchar](200) NULL,
	[BaslangicZamani] [datetime] NULL,
	[BitisZamani] [datetime] NULL,
	[Durum] [nvarchar](20) NULL,
	[OlusturmaTarihi] [datetime] NULL,
	[Aciklama] [nvarchar](255) NULL,
PRIMARY KEY CLUSTERED 
(
	[ArizaID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[IsEmirleri]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[IsEmirleri](
	[EmirID] [int] IDENTITY(1,1) NOT NULL,
	[MakineID] [int] NOT NULL,
	[UrunAdi] [nvarchar](50) NULL,
	[HedefAdet] [int] NULL,
	[BaslangicTarihi] [datetime] NULL,
	[Durum] [nvarchar](20) NULL,
PRIMARY KEY CLUSTERED 
(
	[EmirID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Kullanicilar]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Kullanicilar](
	[KullaniciID] [int] IDENTITY(1,1) NOT NULL,
	[AdSoyad] [nvarchar](50) NOT NULL,
	[KullaniciAdi] [nvarchar](30) NOT NULL,
	[Sifre] [nvarchar](50) NOT NULL,
	[Rol] [nvarchar](20) NOT NULL,
	[KayitTarihi] [datetime] NULL,
	[Durum] [bit] NULL,
	[ProfilResmi] [varbinary](max) NULL,
	[SicilNo] [nvarchar](20) NULL,
	[TcNo] [nvarchar](11) NULL,
PRIMARY KEY CLUSTERED 
(
	[KullaniciID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY],
UNIQUE NONCLUSTERED 
(
	[KullaniciAdi] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY] TEXTIMAGE_ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Makineler]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Makineler](
	[MakineID] [int] IDENTITY(1,1) NOT NULL,
	[MakineAdi] [nvarchar](50) NOT NULL,
	[Konum] [nvarchar](50) NULL,
	[Durum] [nvarchar](50) NULL,
	[IPAdresi] [nvarchar](15) NULL,
	[Model] [nvarchar](50) NULL,
	[SeriNo] [nvarchar](50) NULL,
	[UretimHatti] [nvarchar](50) NULL,
	[BakimSikligiGun] [int] NULL,
	[SonBakimTarihi] [datetime] NULL,
	[KayitTarihi] [datetime] NULL,
	[Operator] [nvarchar](50) NULL,
	[PLC_Durum] [nvarchar](50) NULL,
PRIMARY KEY CLUSTERED 
(
	[MakineID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Tbl_Bildirimler]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Tbl_Bildirimler](
	[ID] [int] IDENTITY(1,1) NOT NULL,
	[Baslik] [nvarchar](50) NULL,
	[Mesaj] [nvarchar](250) NULL,
	[Tarih] [datetime] NULL,
	[Durum] [bit] NULL,
PRIMARY KEY CLUSTERED 
(
	[ID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[Uretimler]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[Uretimler](
	[UretimID] [int] IDENTITY(1,1) NOT NULL,
	[MakineID] [int] NOT NULL,
	[PersonelID] [int] NOT NULL,
	[UrunTipi] [nvarchar](100) NULL,
	[HedefMiktar] [int] NULL,
	[UretilenMiktar] [int] NULL,
	[BaslangicZamani] [datetime] NULL,
	[BitisZamani] [datetime] NULL,
	[Durum] [nvarchar](20) NULL,
PRIMARY KEY CLUSTERED 
(
	[UretimID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
/****** Object:  Table [dbo].[UretimLoglari]    Script Date: 21.12.2025 18:45:55 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE TABLE [dbo].[UretimLoglari](
	[LogID] [bigint] IDENTITY(1,1) NOT NULL,
	[MakineID] [int] NOT NULL,
	[Sicaklik] [decimal](5, 2) NULL,
	[Basinc] [decimal](5, 2) NULL,
	[Hiz] [int] NULL,
	[UretilenAdet] [int] NULL,
	[KayitZamani] [datetime] NULL,
PRIMARY KEY CLUSTERED 
(
	[LogID] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON, OPTIMIZE_FOR_SEQUENTIAL_KEY = OFF) ON [PRIMARY]
) ON [PRIMARY]
GO
ALTER TABLE [dbo].[Arizalar] ADD  DEFAULT (getdate()) FOR [BaslangicZamani]
GO
ALTER TABLE [dbo].[Arizalar] ADD  DEFAULT ('Aktif') FOR [Durum]
GO
ALTER TABLE [dbo].[Arizalar] ADD  DEFAULT (getdate()) FOR [OlusturmaTarihi]
GO
ALTER TABLE [dbo].[IsEmirleri] ADD  DEFAULT (getdate()) FOR [BaslangicTarihi]
GO
ALTER TABLE [dbo].[IsEmirleri] ADD  DEFAULT ('Bekliyor') FOR [Durum]
GO
ALTER TABLE [dbo].[Kullanicilar] ADD  DEFAULT (getdate()) FOR [KayitTarihi]
GO
ALTER TABLE [dbo].[Kullanicilar] ADD  DEFAULT ((1)) FOR [Durum]
GO
ALTER TABLE [dbo].[Makineler] ADD  DEFAULT (getdate()) FOR [KayitTarihi]
GO
ALTER TABLE [dbo].[Tbl_Bildirimler] ADD  DEFAULT (getdate()) FOR [Tarih]
GO
ALTER TABLE [dbo].[Tbl_Bildirimler] ADD  DEFAULT ((0)) FOR [Durum]
GO
ALTER TABLE [dbo].[Uretimler] ADD  DEFAULT ((0)) FOR [UretilenMiktar]
GO
ALTER TABLE [dbo].[Uretimler] ADD  DEFAULT (getdate()) FOR [BaslangicZamani]
GO
ALTER TABLE [dbo].[Uretimler] ADD  DEFAULT ('DevamEdiyor') FOR [Durum]
GO
ALTER TABLE [dbo].[UretimLoglari] ADD  DEFAULT (getdate()) FOR [KayitZamani]
GO
ALTER TABLE [dbo].[Arizalar]  WITH CHECK ADD  CONSTRAINT [FK_Ariza_Makine] FOREIGN KEY([MakineID])
REFERENCES [dbo].[Makineler] ([MakineID])
GO
ALTER TABLE [dbo].[Arizalar] CHECK CONSTRAINT [FK_Ariza_Makine]
GO
ALTER TABLE [dbo].[IsEmirleri]  WITH CHECK ADD  CONSTRAINT [FK_Emir_Makine] FOREIGN KEY([MakineID])
REFERENCES [dbo].[Makineler] ([MakineID])
GO
ALTER TABLE [dbo].[IsEmirleri] CHECK CONSTRAINT [FK_Emir_Makine]
GO
ALTER TABLE [dbo].[Uretimler]  WITH CHECK ADD  CONSTRAINT [FK_Uretim_Makine] FOREIGN KEY([MakineID])
REFERENCES [dbo].[Makineler] ([MakineID])
GO
ALTER TABLE [dbo].[Uretimler] CHECK CONSTRAINT [FK_Uretim_Makine]
GO
ALTER TABLE [dbo].[UretimLoglari]  WITH CHECK ADD  CONSTRAINT [FK_Log_Makine] FOREIGN KEY([MakineID])
REFERENCES [dbo].[Makineler] ([MakineID])
GO
ALTER TABLE [dbo].[UretimLoglari] CHECK CONSTRAINT [FK_Log_Makine]
GO
USE [master]
GO
ALTER DATABASE [AkilliFabrikaDB] SET  READ_WRITE 
GO

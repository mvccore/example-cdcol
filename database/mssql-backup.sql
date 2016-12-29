USE [master]
GO

CREATE DATABASE [cdcol]
GO

USE [cdcol]
GO

CREATE TABLE [dbo].[cds](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[title] [nvarchar](200) NOT NULL,
	[interpret] [nvarchar](200) NOT NULL,
	[year] [int] NULL CONSTRAINT [DF__cds__year__0F975522]  DEFAULT ((0)),
	CONSTRAINT [PK_cds] PRIMARY KEY CLUSTERED (
		[id] ASC
	) WITH (
		PAD_INDEX = OFF, 
		STATISTICS_NORECOMPUTE = OFF, 
		IGNORE_DUP_KEY = OFF, 
		ALLOW_ROW_LOCKS = ON, 
		ALLOW_PAGE_LOCKS = ON
	) ON [PRIMARY]
) ON [PRIMARY]
GO

CREATE TABLE [dbo].[users](
	[id] [int] IDENTITY(1,1) NOT NULL,
	[user_name] [varchar](50) NOT NULL,
	[password_hash] [varchar](40) NOT NULL,
	[full_name] [nvarchar](100) NOT NULL,
	CONSTRAINT [PK_users] PRIMARY KEY CLUSTERED (
		[id] ASC
	) WITH (
		PAD_INDEX = OFF, 
		STATISTICS_NORECOMPUTE = OFF, 
		IGNORE_DUP_KEY = OFF, 
		ALLOW_ROW_LOCKS = ON, 
		ALLOW_PAGE_LOCKS = ON
	) ON [PRIMARY]
) ON [PRIMARY]
GO

SET IDENTITY_INSERT [dbo].[cds] ON 
INSERT [dbo].[cds] ([id], [title], [interpret], [year]) VALUES (1, N'Jump', N'Van Halen', 1984)
INSERT [dbo].[cds] ([id], [title], [interpret], [year]) VALUES (2, N'Hey Boy Hey Girl', N'The Chemical Brothers', 1999)
INSERT [dbo].[cds] ([id], [title], [interpret], [year]) VALUES (3, N'Black Light', N'Groove Armada', 2010)
INSERT [dbo].[cds] ([id], [title], [interpret], [year]) VALUES (4, N'Hotel', N'Moby', 2005)
INSERT [dbo].[cds] ([id], [title], [interpret], [year]) VALUES (5, N'Berlin Calling', N'Paul Kalkbrenner', 2008)
SET IDENTITY_INSERT [dbo].[cds] OFF
GO

SET IDENTITY_INSERT [dbo].[users] ON
INSERT [dbo].[users] ([id], [user_name], [password_hash], [full_name]) 
VALUES (1, N'admin', N'0c0fe72aaae872f5444b2d1c04f89d78b5df48a8', N'Administrator') -- password is "demo"
SET IDENTITY_INSERT [dbo].[users] OFF
GO

CREATE NONCLUSTERED INDEX [IX_cds_interpret] ON [dbo].[cds] (
	[interpret] ASC
) WITH (
	PAD_INDEX = OFF, 
	STATISTICS_NORECOMPUTE = OFF, 
	SORT_IN_TEMPDB = OFF, 
	DROP_EXISTING = OFF, 
	ONLINE = OFF, 
	ALLOW_ROW_LOCKS = ON, 
	ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_cds_title] ON [dbo].[cds] (
	[title] ASC
) WITH (
	PAD_INDEX = OFF, 
	STATISTICS_NORECOMPUTE = OFF, 
	SORT_IN_TEMPDB = OFF, 
	DROP_EXISTING = OFF, 
	ONLINE = OFF, 
	ALLOW_ROW_LOCKS = ON, 
	ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]
GO

CREATE NONCLUSTERED INDEX [IX_cds_year] ON [dbo].[cds] (
	[year] ASC
) WITH (
	PAD_INDEX = OFF, 
	STATISTICS_NORECOMPUTE = OFF, 
	SORT_IN_TEMPDB = OFF, 
	DROP_EXISTING = OFF, 
	ONLINE = OFF, 
	ALLOW_ROW_LOCKS = ON, 
	ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]
GO

ALTER TABLE [dbo].[users] ADD  CONSTRAINT [IX_users_user_name] UNIQUE NONCLUSTERED (
	[user_name] ASC
) WITH (
	PAD_INDEX = OFF, 
	STATISTICS_NORECOMPUTE = OFF, 
	SORT_IN_TEMPDB = OFF, 
	IGNORE_DUP_KEY = OFF, 
	ONLINE = OFF, 
	ALLOW_ROW_LOCKS = ON, 
	ALLOW_PAGE_LOCKS = ON
) ON [PRIMARY]
GO
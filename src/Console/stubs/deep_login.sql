-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- 主机： localhost
-- 生成日期： 2022-04-10 20:09:46
-- 服务器版本： 5.7.30-log
-- PHP 版本： 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

--
-- 表的结构 `api_tokens`
--

CREATE TABLE `api_tokens`
(
    `id`         int(10) UNSIGNED                       NOT NULL,
    `api_token`  varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_id`    bigint(20) UNSIGNED                    NOT NULL,
    `created_at` timestamp                              NULL DEFAULT NULL,
    `updated_at` timestamp                              NULL DEFAULT NULL,
    `expire_at`  int(10) UNSIGNED                            DEFAULT '0' COMMENT ' 过期时间'
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `members`
--

CREATE TABLE `members`
(
    `uid`             int(10) UNSIGNED     DEFAULT NULL COMMENT '用户ID',
    `nickname`        varchar(64) NOT NULL DEFAULT '' COMMENT '昵称',
    `sex`             tinyint(3) UNSIGNED  DEFAULT '0' COMMENT '性别',
    `head_pic`        varchar(255)         DEFAULT NULL,
    `head_pic_small`  varchar(255)         DEFAULT NULL,
    `head_pic_src`    varchar(255)         DEFAULT NULL,
    `birthday`        date                 DEFAULT NULL COMMENT '生日',
    `qq`              char(10)             DEFAULT '' COMMENT 'qq号',
    `score`           mediumint(8)         DEFAULT '0' COMMENT '用户积分',
    `login`           int(10) UNSIGNED     DEFAULT '0' COMMENT '登录次数',
    `reg_ip`          bigint(20)           DEFAULT '0' COMMENT '注册IP',
    `reg_time`        int(10) UNSIGNED     DEFAULT '0' COMMENT '注册时间',
    `last_login_ip`   bigint(20)           DEFAULT '0' COMMENT '最后登录IP',
    `last_login_time` int(10) UNSIGNED     DEFAULT '0' COMMENT '最后登录时间',
    `status`          tinyint(4)           DEFAULT '0' COMMENT '会员状态',
    `notify`          int(2)               DEFAULT '1',
    `pushPlatform`    int(2)               DEFAULT '0',
    `clientId`        varchar(255)         DEFAULT '0',
    `regId`           varchar(255)         DEFAULT '0',
    `deviceToken`     varchar(255)         DEFAULT '0',
    `alias`           varchar(255)         DEFAULT '0',
    `city`            varchar(64)          DEFAULT NULL COMMENT '城市(微信)',
    `province`        varchar(64)          DEFAULT NULL COMMENT '省(微信)',
    `country`         varchar(32)          DEFAULT NULL COMMENT '国家(微信)',
    `language`        varchar(32)          DEFAULT NULL COMMENT '语言(微信)',
    `xiaoe_id`        varchar(64)          DEFAULT NULL COMMENT '小鹅通用户id',
    `coin`            int(10) UNSIGNED     DEFAULT '0',
    `update_time`     int(10) UNSIGNED     DEFAULT NULL,
    `imei`            varchar(128)         DEFAULT NULL,
    `android_id`      varchar(128)         DEFAULT NULL,
    `idfa`            varchar(128)         DEFAULT NULL,
    `idfv`            varchar(128)         DEFAULT NULL,
    `appid`           varchar(128)         DEFAULT NULL,
    `channel`         varchar(256)         DEFAULT NULL,
    `os_type`         varchar(256)         DEFAULT NULL,
    `deleted_at`      timestamp   NULL     DEFAULT NULL,
    `create_time`     int(11)              DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8 COMMENT ='会员表'
  ROW_FORMAT = DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `ucenter_members`
--

CREATE TABLE `ucenter_members`
(
    `id`              int(10) UNSIGNED NOT NULL COMMENT '用户ID',
    `username`        varchar(64)      NOT NULL COMMENT '用户名，手机注册时候是手机号，微信注册时是unionid',
    `password`        varchar(64)      NOT NULL COMMENT '密码',
    `email`           varchar(64)      NOT NULL COMMENT '用户邮箱',
    `mobile`          char(32)         NOT NULL DEFAULT '' COMMENT '用户手机',
    `phone`           char(15)                  DEFAULT NULL COMMENT '小鹅通绑定手机号',
    `reg_time`        int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '注册时间',
    `reg_ip`          bigint(20)       NOT NULL DEFAULT '0' COMMENT '注册IP',
    `last_login_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后登录时间',
    `last_login_ip`   bigint(20)       NOT NULL DEFAULT '0' COMMENT '最后登录IP',
    `update_time`     int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '更新时间',
    `status`          tinyint(4)                DEFAULT '0' COMMENT '用户状态',
    `user_type`       tinyint(4)       NOT NULL COMMENT '0手机注册，1微信',
    `unionid`         varchar(128)              DEFAULT NULL COMMENT '微信unionid',
    `access_token`    varchar(128)              DEFAULT NULL COMMENT '微信的access_token',
    `expires_in`      mediumint(9)              DEFAULT NULL COMMENT '微信授权过期时间',
    `refresh_token`   varchar(128)              DEFAULT NULL COMMENT '微信刷新token',
    `scope`           varchar(128)              DEFAULT NULL COMMENT '微信授权范围'
) ENGINE = MyISAM
  DEFAULT CHARSET = utf8 COMMENT ='用户表';

-- --------------------------------------------------------

--
-- 表的结构 `verify_codes`
--

CREATE TABLE `verify_codes`
(
    `id`         int(11)     NOT NULL,
    `mobile`     varchar(32) NOT NULL COMMENT '手机号',
    `code`       varchar(6)  NOT NULL COMMENT '验证码',
    `ctime`      timestamp   NULL     DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `utime`      timestamp   NULL     DEFAULT CURRENT_TIMESTAMP COMMENT '使用时间',
    `status`     tinyint(4)  NOT NULL DEFAULT '0' COMMENT '-1失效0未使用1已使用',
    `sendStatus` tinyint(4)  NOT NULL DEFAULT '-1' COMMENT '0表示发送 1表示发送成功，2表示发送失败',
    `sendTime`   timestamp   NULL     DEFAULT NULL,
    `smsCreated` varchar(16)          DEFAULT NULL,
    `smsSid`     varchar(32)          DEFAULT NULL,
    `smsStatus`  varchar(16)          DEFAULT NULL,
    `client_ip`  varchar(16)          DEFAULT NULL,
    `created_at` int(10) UNSIGNED     DEFAULT NULL,
    `updated_at` int(10) UNSIGNED     DEFAULT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

--
-- 转储表的索引
--

--
-- 表的索引 `api_tokens`
--
ALTER TABLE `api_tokens`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `api_tokens_api_token_unique` (`api_token`);

--
-- 表的索引 `ucenter_members`
--
ALTER TABLE `ucenter_members`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `username` (`username`),
    ADD UNIQUE KEY `email` (`email`),
    ADD KEY `status` (`status`);

--
-- 表的索引 `verify_codes`
--
ALTER TABLE `verify_codes`
    ADD PRIMARY KEY (`id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `api_tokens`
--
ALTER TABLE `api_tokens`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `ucenter_members`
--
ALTER TABLE `ucenter_members`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户ID';

--
-- 使用表AUTO_INCREMENT `verify_codes`
--
ALTER TABLE `verify_codes`
    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;

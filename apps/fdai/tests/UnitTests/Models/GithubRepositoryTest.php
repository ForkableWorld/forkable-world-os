<?php

namespace Tests\UnitTests\Models;

use App\Models\GithubRepository;
use App\Types\MySQLTypes;
use App\Types\PhpTypes;
use App\Types\QMStr;
use Tests\UnitTestCase;

/**
 * @package Tests\UnitTests\Files
 * @coversDefaultClass \App\Models\GithubRepository
 */
class GithubRepositoryTest extends UnitTestCase
{
	public function testGithubSearch(){
        //GithubRepository::generateProperties();
        $model = new GithubRepository();
		$fillable = $model->getFillable();
		$this->assertContains('id', $fillable);
        $attributes = [
            'id' => 33331247,
            'node_id' => 'MDEwOlJlcG9zaXRvcnkzMzMzMTI0Nw==',
            'name' => 'china-dictatorship',
            'full_name' => 'cirosantilli/china-dictatorship',
            'private' => false,
            'owner' =>
                [
                    'login' => 'cirosantilli',
                    'id' => 1429315,
                    'node_id' => 'MDQ6VXNlcjE0MjkzMTU=',
                    'avatar_url' => 'https://avatars.githubusercontent.com/u/1429315?v=4',
                    'gravatar_id' => '',
                    'url' => 'https://api.github.com/users/cirosantilli',
                    'html_url' => 'https://github.com/cirosantilli',
                    'followers_url' => 'https://api.github.com/users/cirosantilli/followers',
                    'following_url' => 'https://api.github.com/users/cirosantilli/following{/other_user}',
                    'gists_url' => 'https://api.github.com/users/cirosantilli/gists{/gist_id}',
                    'starred_url' => 'https://api.github.com/users/cirosantilli/starred{/owner}{/repo}',
                    'subscriptions_url' => 'https://api.github.com/users/cirosantilli/subscriptions',
                    'organizations_url' => 'https://api.github.com/users/cirosantilli/orgs',
                    'repos_url' => 'https://api.github.com/users/cirosantilli/repos',
                    'events_url' => 'https://api.github.com/users/cirosantilli/events{/privacy}',
                    'received_events_url' => 'https://api.github.com/users/cirosantilli/received_events',
                    'type' => 'User',
                    'site_admin' => false,
                ],
            'html_url' => 'https://github.com/cirosantilli/china-dictatorship',
            'description' => '反中共政治宣传库。Anti Chinese government propaganda. 住在中国真名用户的网友请别给星星，不然你要被警察请喝茶。常见问答集，新闻集和饭店和音乐建议。卐习万岁卐。冠状病毒审查郝海东新疆改造中心六四事件法轮功 996.ICU709大抓捕巴拿马文件邓家贵低端人口西藏骚乱。Friends who live in China and have real name on account, please don\'t star this repo, or else the police might pay you a visit.  Home to the mega-FAQ, news compilation, restaurant and music recommendations.Heil Xi 卐. 大陆修宪香港恶法台湾武统朝鲜毁约美中冷战等都是王沪宁愚弄习思想极左命运共同体的大策划中共窃国这半个多世纪所犯下的滔天罪恶，前期是毛泽东策划的，中期6.4前后是邓小平策划的，黄牛数据分析后期是毛的极左追随者三朝罪恶元凶王沪宁策划的。王沪宁高小肆业因文革政治和情报需要保送“学院外语班“红色仕途翻身，所以王的本质是极左的。他是在上海底层弄堂长大的，因其本性也促成其瘪三下三滥个性，所以也都说他有易主“变色龙”哈巴狗“的天性。大陆像王沪宁这样学马列政治所谓"法学"专业的人，在除朝鲜古巴所有国家特别是在文明发达国家是无法找到专业对口工作必定失业，唯独在大陆却是重用的紧缺“人才”，6.4后中共信仰大危机更是最重用的救党“人才”。这也就是像王沪宁此类工农兵假“大学生”平步青云的原因，他们最熟悉毛泽东历次运动的宫庭内斗经验手段和残酷的阶级斗争等暴力恐怖的“政治学”。王沪宁能平步青云靠他这马毛伪“政治学”资本和头衔，不是什么真才实学，能干实事有点真才实学的或许在他手下的谋士及秘书班子中可以找到。王沪宁的“真才实学”只不过是一个只读四年小学的人，大半辈子在社会上磨炼特别是在中共官场滚打炼出的的手段和经验而已，他和习近平等保送的工农兵假“大学生”都一样，无法从事原“专业”都凭红资本而从政。六四学运期间各界一边倒支持学生，王沪宁一度去法国躲避和筹谋，他还加入了反学运签名，成为极少有的反学运者仕途突显，在六四和苏联垮台后中共意识形态危机，江泽民上台看上唯一能应急的王沪宁聚谋士泡制的"稳定统一领导"和之后的"新权威"谬论。左转被邓小平南巡阻止后，王策划顺邓经济改革却将政治改革逐步全面终止和倒退，泡制“三个代表”为极左转建立庞大牢固的红色既得利益集团。因此六四后各重大决策和危机难题都摆在中共中央政策研究室王沪宁桌面上，使王沪宁成了此后中共三朝都无法摆脱的幕后最有决策性实权的人，中共中央政策研究室是王为其野心巨资经营几十年，聚众谋士的间谍情报汇总研究的特务机关和策划制定决策重要机构与基地，王沪宁本人和决定其仕途关键的首任岳父及家属就有情报工作背景。中央政研室重要到王沪宁入常后为了死抓这中共情报与决策大权，宁可放弃国家副主席和中央党校校长。后再加个除习外唯他担任的中共几核心领导小组之一的“不忘初心牢记使命”主题教育工作小组组长。此后他把持的舆论必将以宣传“不忘初心牢记使命”为主，打造众所周知的所谓“习思想”其实是”王思想“。王自从主导中央政研室开始决策后，策划中止邓小平的与美妥协路线回归毛极左的反美路线。帮助前南斯拉夫提供情报打落美机放中使馆引发炸使馆事件，以此掀起六四后唯一的全国大规模游行并借此反美而起家。后又帮江泽民提供法轮功会是超过中共组织的情报，策划决策镇压迫害开始并没有把矛头指向江的法轮功群体，策划决定阻止党内外近三十年来平反六四的呼声。致远黑皮书马拉松程序员易支付英语台词文字匹配美团点评各业务线提供知识库团队共享阿里云高精Excel识别德讯 ·吉特胡布薄熙来黑科技习近平讲话模拟器习近平音源黑马程序员MySQL数据库玉米杂草数据集销售系统开发疫情期间网民情绪识别比赛996icu996 icu学习强国预测结果导出赖伟林刺杀小说家购物商场英语词汇量小程序联级选择器Bitcoin区块链 技术面试必备基础知识 Leetcode 计算机操作系统 计算机网络 系统设计 Java学习 面试指南 一份涵盖大部分 Java 程序员所需要掌握的核心知识 准备 Java 面试 首选 JavaGuide Python 1 天从新手到大师刷算法全靠套路 认准 labuladong 就够了 免费的计算机编程类中文书籍 欢迎投稿用动画的形式呈现解LeetCode题目的思路 互联网 Java 工程师进阶知识完全扫盲 涵盖高并发 分布式 高可用 微服务 海量数据处理等领域知识后端架构师技术图谱mall项目是一套电商系统 包括前台商城系统及后台管理系统 基于SpringBoot MyBatis实现 采用Docker容器化部署 前台商城系统包含首页门户 商品推荐 商品搜索 商品展示 购物车 订单流程 会员中心 客户服务 帮助中心等模块 后台管理系统包含商品管理 订单管理 会员管理 促销管理 运营管理 内容管理 统计报表 财务管理 权限管理 设置等模块 微信小程序开发资源汇总 最全中华古诗词数据库 唐宋两朝近一万四千古诗人 接近5 5万首唐诗加26万宋诗 两宋时期1564位词人 21 5 首词 uni app 是使用 Vue 语法开发小程序 H5 App的统一框架2 21年最新总结 阿里 腾讯 百度 美团 头条等技术面试题目 以及答案 专家出题人分析汇总 科学上网 自由上网 翻墙 软件 方法 一键翻墙浏览器 免费账号 节点分享 vps一键搭建脚本 教程AiLearning 机器学习 MachineLearning ML 深度学习 DeepLearning DL 自然语言处理 NLP123 6智能刷票 订票开放式跨端跨框架解决方案 支持使用 React Vue Nerv 等框架来开发微信 京东 百度 支付宝 字节跳动 QQ 小程序 H5 React Native 等应用 taro zone 掘金翻译计划 可能是世界最大最好的英译中技术社区 最懂读者和译者的翻译平台  no evil 程序员找工作黑名单 换工作和当技术合伙人需谨慎啊 更新有赞 算法面试 算法知识 针对小白的算法训练 还包括 1 阿里 字节 滴滴 百篇大厂面经汇总 2 千本开源电子书 3 百张思维导图 右侧来个 star 吧 English version supported 955 不加班的公司名单 工作 955 work–life balance 工作与生活的平衡 诊断利器Arthas The Way to Go 中文译本 中文正式名 Go 入门指南  Java面试 Java学习指南 一份涵盖大部分Java程序员所需要掌握的核心知识 教程 技术栈示例代码 快速简单上手教程 2 17年买房经历总结出来的买房购房知识分享给大家 希望对大家有所帮助 买房不易 且买且珍惜http下载工具 基于http代理 支持多连接分块下载 动手学深度学习 面向中文读者 能运行 可讨论 中英文版被全球175所大学采用教学 阿里云计算平台团队出品 为监控而生的数据库连接池程序员简历模板系列 包括PHP程序员简历模板 iOS程序员简历模板 Android程序员简历模板 Web前端程序员简历模板 Java程序员简历模板 C C 程序员简历模板 NodeJS程序员简历模板 架构师简历模板以及通用程序员简历模板采用自身模块规范编写的前端 UI 框架 遵循原生 HTML CSS JS 的书写形式 极低门槛 拿来即用 贵校课程资料民间整理 企业级低代码平台 前后端分离架构强大的代码生成器让前后端代码一键生成 无需写任何代码 引领新的开发模式OnlineCoding 代码生成 手工MERGE 帮助Java项目解决7 %重复工作 让开发更关注业务 既能快速提高效率 帮助公司节省成本 同时又不失灵活性 我是依扬 木易杨 公众号 高级前端进阶 作者 每天搞定一道前端大厂面试题 祝大家天天进步 一年后会看到不一样的自己 冴羽写博客的地方 预计写四个系列 JavaScript深入系列 JavaScript专题系列 ES6系列 React系列 中文分词 词性标注 命名实体识别 依存句法分析 语义依存分析 新词发现 关键词短语提取 自动摘要 文本分类聚类 拼音简繁转换 自然语言处理flutter 开发者帮助 APP 包含 flutter 常用 14 组件的demo 演示与中文文档 下拉刷新 上拉加载 二级刷新 淘宝二楼智能下拉刷新框架 支持越界回弹 越界拖动 具有极强的扩展性 集成了几十种炫酷的Header和 Footer 该项目已成功集成 actuator 监控 admin 可视化监控 logback 日志 aopLog 通过AOP记录web请求日志 统一异常处理 json级别和页面级别 freemarker 模板引擎 thymeleaf 模板引擎 Beetl 模板引擎 Enjoy 模板引擎 JdbcTemplate 通用JDBC操作数据库 JPA 强大的ORM框架 mybatis 强大的ORM框架 通用Mapper 快速操作Mybatis PageHelper 通用的Mybatis分页插件 mybatis plus 快速操作Mybatis BeetlSQL 强大的ORM框架 u Python资源大全中文版 包括 Web框架 网络爬虫 模板引擎 数据库 数据可视化 图片处理等 由 开源前哨 和 Python开发者 微信公号团队维护更新 吴恩达老师的机器学习课程个人笔记To Be Top Javaer Java工程师成神之路循序渐进 学习博客Spring系列源码 mrbird cc谢谢可能是让你受益匪浅的英语进阶指南镜像网易云音乐 Node js API service快速 简单避免OOM的java处理Excel工具基于 Vue js 的小程序开发框架 从底层支持 Vue js 语法和构建工具体系 中文版 Apple 官方 Swift 教程本项目曾冲到全球第一 干货集锦见本页面最底部 另完整精致的纸质版 编程之法 面试和算法心得 已在京东 当当上销售好耶 是女装Security Guide for Developers 实用性开发人员安全须知 阿里巴巴 MySQL binlog 增量订阅 消费组件  ECMAScript 6入门 是一本开源的 JavaScript 语言教程 全面介绍 ECMAScript 6 新增的语法特性  C C 技术面试基础知识总结 包括语言 程序库 数据结构 算法 系统 网络 链接装载库等知识及面试经验 招聘 内推等信息 一款优秀的开源博客发布应用  Solutions to LeetCode by Go 1 % test coverage runtime beats 1 % LeetCode 题解分布式任务调度平台XXL JOB  谷粒 Chrome插件英雄榜 为优秀的Chrome插件写一本中文说明书 让Chrome插件英雄们造福人类公众号 加1 同步更新互联网公司技术架构 微信 淘宝 微博 腾讯 阿里 美团点评 百度 Google Facebook Amazon eBay的架构 欢迎PR补充IntelliJ IDEA 简体中文专题教程程序员技能图谱前端面试每日 3 1 以面试题来驱动学习 提倡每日学习与思考 每天进步一点 每天早上5点纯手工发布面试题 死磕自己 愉悦大家 4 道前端面试题全面覆盖小程序 软技能 华为鸿蒙操作系统 互联网首份程序员考公指南 由3位已经进入体制内的前大厂程序员联合献上 Mac微信功能拓展 微信插件 微信小助手 A plugin for Mac WeChat  机器学习 西瓜书 公式推导解析 在线阅读地址一款轻量级 高性能 功能强大的内网穿透代理服务器 支持tcp udp socks5 http等几乎所有流量转发 可用来访问内网网站 本地支付接口调试 ssh访问 远程桌面 内网dns解析 内网socks5代理等等 并带有功能强大的web管理端一款面向泛前端产品研发全生命周期的效率平台 文言文編程語言清华大学计算机系课程攻略面向云原生微服务的高可用流控防护组件  On Java 8 中文版 本文原文由知名 Hacker Eric S Raymond 所撰寫 教你如何正確的提出技術問題並獲得你滿意的答案 React Native指南汇集了各类react native学习资源 开源App和组件1 Days Of ML Code中文版千古前端图文教程 超详细的前端入门到进阶学习笔记 从零开始学前端 做一名精致优雅的前端工程师 公众号 千古壹号 作者 基于 React 的渐进式研发框架 ice work视频播放器支持弹幕 外挂字幕 支持滤镜 水印 gif截图 片头广告 中间广告 多个同时播放 支持基本的拖动 声音 亮度调节 支持边播边缓存 支持视频自带rotation的旋转 9 27 之类 重力旋转与手动旋转的同步支持 支持列表播放 列表全屏动画 视频加载速度 列表小窗口支持拖动 动画效果 调整比例 多分辨率切换 支持切换播放器 进度条小窗口预览 列表切换详情页面无缝播放 rtsp concat mpeg JumpServer 是全球首款开源的堡垒机 是符合 4A 的专业运维安全审计系统 Linux命令大全搜索工具 内容包含Linux命令手册 详解 学习 搜集 git io linux book Node js 包教不包会 by alsotang又一个小商城 litemall Spring Boot后端 Vue管理员前端 微信小程序用户前端 Vue用户移动端微信 跳一跳 Python 辅助Java资源大全中文版 包括开发库 开发工具 网站 博客 微信 微博等 由伯乐在线持续更新  python模拟登陆一些大型网站 还有一些简单的爬虫 希望对你们有所帮助 ️ 如果喜欢记得给个star哦 C 那些事 网络爬虫实战 淘宝 京东 网易云 B站 123 6 抖音 笔趣阁 漫画小说下载 音乐电影下载等deeplearning ai 吴恩达老师的深度学习课程笔记及资源 Spring Boot基础教程 Spring Boot 2 x版本连载中 帮助 Android App 进行组件化改造的路由框架 最接近原生APP体验的高性能框架基于Vue3 Element Plus 的后台管理系统解决方案程序员如何优雅的挣零花钱 2 版 升级为小书了 从Java基础 JavaWeb基础到常用的框架再到面试题都有完整的教程 几乎涵盖了Java后端必备的知识点spring boot 实践学习案例 是 spring boot 初学者及核心技术巩固的最佳实践 另外写博客 用 OpenWrite 最好用的 V2Ray 一键安装脚本 管理脚本中国程序员容易发音错误的单词 统计学习方法 的代码实现关于Python的面试题本项目将 动手学深度学习 Dive into Deep Learning 原书中的MXNet实现改为PyTorch实现 提高 Android UI 开发效率的 UI 库前端精读周刊 帮你理解最前沿 实用的技术  的奇技淫巧时间选择器 省市区三级联动 Python爬虫代理IP池 proxy pool LeetCode 刷题攻略 2 道经典题目刷题顺序 共6 w字的详细图解 视频难点剖析 5 余张思维导图 从此算法学习不再迷茫 来看看 你会发现相见恨晚 一个基于 electron 的音乐软件Flutter 超完整的开源项目 功能丰富 适合学习和日常使用 GSYGithubApp系列的优势 我们目前已经拥有四个版本 功能齐全 项目框架内技术涉及面广 完成度高 持续维护 配套文章 适合全面学习 对比参考 跨平台的开源Github客户端App 更好的体验 更丰富的功能 旨在更好的日常管理和维护个人Github 提供更好更方便的驾车体验Σ 同款Weex版本同款React Native版本 https g 这是一个用于显示当前网速 CPU及内存利用率的桌面悬浮窗软件 并支持任务栏显示 支持更换皮肤 是一个跨平台的强加密无特征的代理软件 零配置 V2rayU 基于v2ray核心的mac版客户端 用于科学上网 使用swift编写 支持vmess shadowsocks socks5等服务协议 支持订阅 支持二维码 剪贴板导入 手动配置 二维码分享等算法模板 最科学的刷题方式 最快速的刷题路径 你值得拥有 经典编程书籍大全 涵盖 计算机系统与网络 系统架构 算法与数据结构 前端开发 后端开发 移动开发 数据库 测试 项目与团队 程序员职业修炼 求职面试等wangEditor 轻量级web富文本框前端跨框架跨平台框架 每个 JavaScript 工程师都应懂的33个概念 leonardomso一个可以观看国内主流视频平台所有视频的客户端Android开发人员不得不收集的工具类集合 支付宝支付 微信支付 统一下单 微信分享 Zip4j压缩 支持分卷压缩与加密 一键集成UCrop选择圆形头像 一键集成二维码和条形码的扫描与生成 常用Dialog WebView的封装可播放视频 仿斗鱼滑动验证码 Toast封装 震动 GPS Location定位 图片缩放 Exif 图片添加地理位置信息 经纬度 蛛网等级 颜色选择器 ArcGis VTPK 编译运行一下说不定会找到惊喜 123 6 购票助手 支持集群 多账号 多任务购票以及 Web 页面管理  编程随想 收藏的电子书清单 多个学科 含下载链接  Banner 2 来了 Android广告图片轮播控件 内部基于ViewPager2实现 Indicator和UI都可以自定义  零代码 热更新 自动化 ORM 库 后端接口和文档零代码 前端 客户端 定制返回 JSON 的数据和结构 Linux Windows macOS 跨平台 V2Ray 客户端 支持使用 C Qt 开发 可拓展插件式设计 walle 瓦力 Devops开源项目代码部署平台基于 node js Mongodb 构建的后台系统 js 源码解析一个涵盖六个专栏分布式消息队列 分布式事务的仓库 希望胖友小手一抖 右上角来个 Star 感恩 1 24基于 vue element ui 的后台管理系统磁力链接聚合搜索中华人民共和国行政区划 省级 省份直辖市自治区 地级 城市 县级 区县 乡级 乡镇街道 村级 村委会居委会 中国省市区镇村二级三级四级五级联动地址数据 iOS开发常用三方库 插件 知名博客等等LeetCode题解 151道题完整版／中文文案排版指北最良心的 Python 教程 业内为数不多致力于极致体验的超强全自研跨平台 windows android iOS 流媒体内核 通过模块化自由组合 支持实时RTMP推流 RTSP推流 RTMP播放器 RTSP播放器 录像 多路流媒体转发 音视频导播 动态视频合成 音频混音 直播互动 内置轻量级RTSP服务等 比快更快 业界真正靠谱的超低延迟直播SDK 1秒内 低延迟模式下2 4 ms  一个 PHP 微信 SDK ️ 跨平台桌面端视频资源播放器 简洁无广告 免费高颜值 后台管理主线版本基于三者并行开发维护 同时支持电脑 手机 平板 切换分支查看不同的vue版本 element plus版本已发布 vue3 vue3 vue vue3 x vue js 程序无国界 但程序员有国界 中国国家尊严不容挑衅 如果您在特殊时期 此项目是机器学习 Machine Learning 深度学习 Deep Learning NLP面试中常考到的知识点和代码实现 也是作为一个算法工程师必会的理论基础知识 夜读 通过 bilibili 在线直播的方式分享 Go 相关的技术话题 每天大家在微信 telegram Slack 上及时沟通交流编程技术话题 GitHubDaily 分享内容定期整理与分类 欢迎推荐 自荐项目 让更多人知道你的项目  支持多家云存储的云盘系统机器学习相关教程DataX是阿里云DataWorks数据集成的开源版本  这里是写博客的地方 Halfrost Field 冰霜之地mall学习教程 架构 业务 技术要点全方位解析 mall项目 4 k star 是一套电商系统 使用现阶段主流技术实现 涵盖了等技术 采用Docker容器化部署  chick 是使用 Node js 和 MongoDB 开发的社区系统一个非常适合IT团队的在线API文档 技术文档工具汇总各大互联网公司容易考察的高频leetcode题 1 Chinese Word Vectors 上百种预训练中文词向量 Android开源弹幕引擎 烈焰弹幕使 ～深度学习框架PyTorch 入门与实战 网易云音乐命令行版本 对开发人员有用的定律 理论 原则和模式TeachYourselfCS 的中文翻译高颜值的第三方网易云播放器 支持 Windows macOS Linux spring cloud vue oAuth2 全家桶实战 前后端分离模拟商城 完整的购物流程 后端运营平台 可以实现快速搭建企业级微服务项目 支持微信登录等三方登录  Chinese sticker pack More joy 表情包的博物馆 Github最有毒的仓库 中国表情包大集合 聚欢乐 Lantern官方版本下载 蓝灯 翻墙 代理 科学上网 外网 加速器 梯子 路由一款入门级的人脸 视频 文字检测以及识别的项目 vue2 vue router vuex 入门项目PanDownload的个人维护版本 一个基于Spring Boot MyBatis的种子项目 用于快速构建中小型API RESTful API项目 iOS interview questions iOS面试题集锦 附答案 学习qq群或 Telegram 群交流为互联网IT人打造的中文版awesome go强大 可定制 易扩展的 ViewPager 指示器框架 是的最佳替代品 支持角标 更支持在非ViewPager场景下使用 使用hide show 切换Fragment或使用se Kubernetes中文指南 云原生应用架构实践手册For macOS 百度网盘 破解SVIP 下载速度限制 架构师技术图谱 助你早日成为架构师mall admin web是一个电商后台管理系统的前端项目 基于Vue Element实现 主要包括商品管理 订单管理 会员管理 促销管理 运营管理 内容管理 统计报表 财务管理 权限管理 设置等功能 网易云音乐第三方 编程随想 整理的 太子党关系网络 专门揭露赵国的权贵基于gin vue搭建的后台管理系统框架 集成jwt鉴权 权限管理 动态路由 分页封装 多点登录拦截 资源权限 上传下载 代码生成器 表单生成器 通用工作流等基础功能 五分钟一套CURD前后端代码 目VUE3版本正在重构 欢迎issue和pr 27天成为Java大神一个基于浏览器端 JS 实现的在线代理编程电子书 电子书 编程书籍 包括人工智能 大数据类 并发编程 数据库类 数据挖掘 新面试题 架构设计 算法系列 计算机类 设计模式 软件测试 重构优化 等更多分类ADB Usage Complete ADB 用法大全二维码生成器 支持 gif 动态图片二维码 Vim 从入门到精通阿布量化交易系统 股票 期权 期货 比特币 机器学习 基于python的开源量化交易 量化投资架构一个简洁优雅的hexo主题 Wiki of OI ICPC for everyone 某大型游戏线上攻略 内含炫酷算术魔法 Google 开源项目风格指南 中文版  Git AWS Google 镜像 SS SSR VMESS节点行业研究报告的知识储备库 cim cross IM 适用于开发者的分布式即时通讯系统微信小程序开源项目库汇总每天更新 全网热门 BT Tracker 列表 天用Go动手写 从零实现系列强大的哔哩哔哩增强脚本 下载视频 音乐 封面 弹幕 简化直播间 评论区 首页 自定义顶栏 删除广告 夜间模式 触屏设备支持Evil Huawei 华为作过的恶Android上一个优雅 万能自定义UI 仿iOS 支持垂直 水平方向切换 支持周视图 自定义周起始 性能高效的日历控件 支持热插拔实现的UI定制 支持标记 自定义颜色 农历 自定义月视图各种显示模式等 Canvas绘制 速度快 占用内存低 你真的想不到日历居然还可以如此优雅已不再维护科学上网插件的离线安装包储存在这里ThinkPHP Framework 十年匠心的高性能PHP框架 Java 程序员眼中的 Linux 一个支持多选 选原图和视频的图片选择器 同时有预览 裁剪功能 支持hsweb haʊs wɛb 是一个基于spring boot 2 x开发 首个使用全响应式编程的企业级后台管理系统基础项目 学习强国 懒人刷分工具 自动学习wxParse 微信小程序富文本解析自定义组件 支持HTML及markdown解析 newbee mall 项目 新蜂商城 是一套电商系统 包括 newbee mall 商城系统及 newbee mall admin 商城后台管理系统 基于 Spring Boot 2 X 及相关技术栈开发 前台商城系统包含首页门户 商品分类 新品上线 首页轮播 商品推荐 商品搜索 商品展示 购物车 订单结算 订单流程 个人订单管理 会员中心 帮助中心等模块 后台管理系统包含数据面板 轮播图管理 商品管理 订单管理 会员管理 分类管理 设置等模块  最全的前端资源汇总仓库 包括前端学习 开发资源 求职面试等 中文翻译手写实现李航 统计学习方法 书中全部算法 Python 抖音机器人 论如何在抖音上找到漂亮小姐姐？  ️A static blog writing client 一个静态博客写作客户端 超级速查表 编程语言 框架和开发工具的速查表 单个文件包含一切你需要知道的东西 迁移学习前端低代码框架 通过 JSON 配置就能生成各种页面 技术面试最后反问面试官的话Machine Learning Yearning 中文版 机器学习训练秘籍 Andrew Ng 著越来越多的网站具有反爬虫特性 有的用图片隐藏关键数据 有的使用反人类的验证码 建立反反爬虫的代码仓库 通过与不同特性的网站做斗争 无恶意 提高技术 欢迎提交难以采集的网站 因工作原因 项目暂停 本项目收藏这些年来看过或者听过的一些不错的常用的上千本书籍 没准你想找的书就在这里呢 包含了互联网行业大多数书籍和面试经验题目等等 有人工智能系列 常用深度学习框架TensorFlow pytorch keras NLP 机器学习 深度学习等等 大数据系列 Spark Hadoop Scala kafka等 程序员必修系列 C C java 数据结构 linux 设计模式 数据库等等  人人影视bot 完全对接人人影视全部无删减资源Spring Cloud基础教程 持续连载更新中一个用于在 macOS 上平滑你的鼠标滚动效果或单独设置滚动方向的小工具 让你的滚轮爽如触控板阿里妈妈前端团队出品的开源接口管理工具RAP第二代超轻量级中文ocr 支持竖排文字识别 支持ncnn mnn tnn推理总模型仅4 7M 微信全平台 SDK Senparc Weixin for C 支持 NET Framework 及 NET Core NET 6 已支持微信公众号 小程序 小游戏 企业号 企业微信 开放平台 微信支付 JSSDK 微信周边等全平台 WeChat SDK for C 中文独立博客列表高效率 QQ 机器人支持库支持定制任何播放器SDK和控制层 OpenPower工作组收集汇总的医院开放数据Xray 基于 Nginx 的 VLESS XTLS 一键安装脚本 FlutterDemo合集 今天你fu了吗莫烦Python 中文AI教学中国特色 TabBar 一行代码实现 Lottie 动画TabBar 支持中间带 号的TabBar样式 自带红点角标 支持动态刷新 Flutter豆瓣客户端 Awesome Flutter Project 全网最1 %还原豆瓣客户端 首页 书影音 小组 市集及个人中心 一个不拉 img xuvip top douyademo mp4 基于SpringCloud2 1的微服务开发脚手架 整合了等 服务治理方面引入等 让项目开发快速进入业务开发 而不需过多时间花费在架构搭建上 持续更新中基于 Vue2 和 ECharts 封装的图表组件 SSR 去广告ACL规则 SS完整GFWList规则 Clash规则碎片 Telegram频道订阅地址和我一步步部署 kubernetes 集群搜集 整理 维护实用规则 中文自然语言处理相关资料基于SOA架构的分布式电商购物商城 前后端分离 前台商城 全家桶 后台管理系统等What happens when 的中文翻译 原仓库QMUI iOS 致力于提高项目 UI 开发效率的解决方案新型冠状病毒防疫信息收集平台告别枯燥 致力于打造 Python 实用小例子在线制作 sorry 为所欲为 的gifNodejs学习笔记以及经验总结 公众号 程序猿小卡 李宏毅 机器学习 笔记 在线阅读地址 Vue js 源码分析V部落 Vue SpringBoot实现的多用户博客管理平台 Android Signature V2 Scheme签名下的新一代渠道包打包神器Autoscroll Banner 无限循环图片 文字轮播器 多种编程语言实现 LeetCode 剑指 Offer 第 2 版 程序员面试金典 第 6 版 题解一套高质量的微信小程序 UI 组件库飞桨 官方模型库 包含多种学术前沿和工业场景验证的深度学习模型 中文 Python 笔记专门为刚开始刷题的同学准备的算法基地 没有最细只有更细 立志用动画将晦涩难懂的算法说的通俗易懂 版入门实例代码 实战教程 是一个高性能且低损耗的 goroutine 池 CVPR 2 21 论文和开源项目合集有 有  Python进阶 Intermediate Python 中文版 机器人视觉 移动机器人 VS SLAM ORB SLAM2 深度学习目标检测 yolov3 行为检测 opencv PCL 机器学习 无人驾驶后台管理系统解决方案创建在线课程 学术简历或初创网站  Chrome插件开发全攻略 配套完整Demo 欢迎clone体验QUANTAXIS 支持任务调度 分布式部署的 股票 期货 期权 港股 虚拟货币 数据 回测 模拟 交易 可视化 多账户 纯本地量化解决方案微信调试 各种WebView样式调试 手机浏览器的页面真机调试 便捷的远程调试手机页面 抓包工具 支持 HTTPS 无需USB连接设备 rich text 富文本编辑器 汉字拼音 hàn zì pīn yīn面向开发人员梳理的代码安全指南以撸代码的形式学习Python提供同花顺客户端 国金 华泰客户端 雪球的基金 股票自动程序化交易以及自动打新 支持跟踪 joinquant ricequant 模拟交易 和 实盘雪球组合 量化交易组件搜狐视频 sohu tv Redis私有云平台spring boot打造文件文档在线预览项目计算机基础 计算机网络 操作系统 数据库 Git 面试问题全面总结 包含详细的follow up question以及答案 全部采用 问题 追问 答案 的形式 即拿即用 直击互联网大厂面试 可用于模拟面试 面试前复习 短期内快速备战面试 首款微信 macOS 客户端撤回拦截与多开windows kernel exploits Windows平台提权漏洞集合权限管理系统 预览地址 47 1 4 7 138 loginpkuseg多领域中文分词工具一款完善的安全评估工具 支持常见 web 安全问题扫描和自定义 poc 使用之前务必先阅读文档零反射全动态Android插件框架Python入门网络爬虫之精华版分布式配置管理平台 中文 iOS Mac 开发博客列表周志华 机器学习 又称西瓜书是一本较为全面的书籍 书中详细介绍了机器学习领域不同类型的算法 例如 监督学习 无监督学习 半监督学习 强化学习 集成降维 特征选择等 记录了本人在学习过程中的理解思路与扩展知识点 希望对新人阅读西瓜书有所帮助  国内首个Spring Cloud微服务化RBAC的管理平台 核心采用前端采用d2 admin中台框架 记得上边点个star 关注更新Apache ECharts incubating 的微信小程序版本C 资源大全中文版 标准库 Web应用框架 人工智能 数据库 图片处理 机器学习 日志 代码分析等 由 开源前哨 和 CPP开发者 微信公号团队维护更新 stackoverflow上Java相关回答整理翻译 基于Google Flutter的WanAndroid客户端 支持Android和iOS 包括BLoC RxDart 国际化 主题色 启动页 引导页  本代码库是作者小傅哥多年从事一线互联网 Java 开发的学习历程技术汇总 旨在为大家提供一个清晰详细的学习教程 侧重点更倾向编写Java核心内容 如果本仓库能为您提供帮助 请给予支持 关注 点赞 分享 C 资源大全中文版 包括了 构建系统 编译器 数据库 加密 初中高的教程 指南 书籍 库等  NET m3u8 downloader 开源的命令行m3u8 HLS dash下载器 支持普通AES 128 CBC解密 多线程 自定义请求头等 支持简体中文 繁体中文和英文 English Supported 国内低代码平台从业者交流tcc transaction是TCC型事务java实现设计模式 Golang实现 研磨设计模式 读书笔记Vue数据可视化组件库 类似阿里DataV 大屏数据展示 提供SVG的边框及装饰 图表 水位图 飞线图等组件 简单易用 长期更新 React版已发布 自己动手做聊天机器人教程 RecyclerView侧滑菜单 Item拖拽 滑动删除Item 自动加载更多 HeaderView FooterView Item分组黏贴 腾讯物联网终端操作系统一个小巧 轻量的浏览器内核 用来取代wke和libcef包含美颜等4 余种实时滤镜相机 可拍照 录像 图片修改springboot 框架与其它组件结合如等用深度学习对对联  技术面试必备基础知识 Leetcode 计算机操作系统 计算机网络 系统设计 Java学习 面试指南 一份涵盖大部分 Java 程序员所需要掌握的核心知识 准备 Java 面试 首选 JavaGuide 用动画的形式呈现解LeetCode题目的思路 互联网 Java 工程师进阶知识完全扫盲 涵盖高并发 分布式 高可用 微服务 海量数据处理等领域知识mall项目是一套电商系统 包括前台商城系统及后台管理系统 基于SpringBoot MyBatis实现 采用Docker容器化部署 前台商城系统包含首页门户 商品推荐 商品搜索 商品展示 购物车 订单流程 会员中心 客户服务 帮助中心等模块 后台管理系统包含商品管理 订单管理 会员管理 促销管理 运营管理 内容管理 统计报表 财务管理 权限管理 设置等模块  GitHub中文排行榜 帮助你发现高分优秀中文项目 更高效地吸收国人的优秀经验成果 榜单每周更新一次 敬请关注  算法面试 算法知识 针对小白的算法训练 还包括 1 阿里 字节 滴滴 百篇大厂面经汇总 2 千本开源电子书 3 百张思维导图 右侧来个 star 吧 English version supported 诊断利器Arthas教程 技术栈示例代码 快速简单上手教程 http下载工具 基于http代理 支持多连接分块下载阿里云计算平台团队出品 为监控而生的数据库连接池 企业级低代码平台 前后端分离架构强大的代码生成器让前后端代码一键生成 无需写任何代码 引领新的开发模式OnlineCoding 代码生成 手工MERGE 帮助Java项目解决7 %重复工作 让开发更关注业务 既能快速提高效率 帮助公司节省成本 同时又不失灵活性  下拉刷新 上拉加载 二级刷新 淘宝二楼智能下拉刷新框架 支持越界回弹 越界拖动 具有极强的扩展性 集成了几十种炫酷的Header和 Footer 该项目已成功集成 actuator 监控 admin 可视化监控 logback 日志 aopLog 通过AOP记录web请求日志 统一异常处理 json级别和页面级别 freemarker 模板引擎 thymeleaf 模板引擎 Beetl 模板引擎 Enjoy 模板引擎 JdbcTemplate 通用JDBC操作数据库 JPA 强大的ORM框架 mybatis 强大的ORM框架 通用Mapper 快速操作Mybatis PageHelper 通用的Mybatis分页插件 mybatis plus 快速操作Mybatis BeetlSQL 强大的ORM框架 u 微人事是一个前后端分离的人力资源管理系统 项目采用SpringBoot Vue开发  秒杀系统设计与实现 互联网工程师进阶与分析 To Be Top Javaer Java工程师成神之路循序渐进 学习博客Spring系列源码 mrbird cc快速 简单避免OOM的java处理Excel工具阿里巴巴 MySQL binlog 增量订阅 消费组件  一款优秀的开源博客发布应用 分布式任务调度平台XXL JOB 一款面向泛前端产品研发全生命周期的效率平台 面向云原生微服务的高可用流控防护组件 视频播放器支持弹幕 外挂字幕 支持滤镜 水印 gif截图 片头广告 中间广告 多个同时播放 支持基本的拖动 声音 亮度调节 支持边播边缓存 支持视频自带rotation的旋转 9 27 之类 重力旋转与手动旋转的同步支持 支持列表播放 列表全屏动画 视频加载速度 列表小窗口支持拖动 动画效果 调整比例 多分辨率切换 支持切换播放器 进度条小窗口预览 列表切换详情页面无缝播放 rtsp concat mpeg 又一个小商城 litemall Spring Boot后端 Vue管理员前端 微信小程序用户前端 Vue用户移动端基于Spring SpringMVC Mybatis分布式敏捷开发系统架构 提供整套公共微服务服务模块 集中权限管理 单点登录 内容管理 支付中心 用户管理 支持第三方登录 微信平台 存储系统 配置中心 日志分析 任务和通知等 支持服务治理 监控和追踪 努力为中小型企业打造全方位J2EE企业级开发解决方案 项目基于的前后端分离的后台管理系统 项目采用分模块开发方式 权限控制采用 RBAC 支持数据字典与数据权限管理 支持一键生成前后端代码 支持动态路由 史上最简单的Spring Cloud教程源码 CAT 作为服务端项目基础组件 提供了 Java C C Node js Python Go 等多语言客户端 已经在美团点评的基础架构中间件框架 MVC框架 RPC框架 数据库框架 缓存框架等 消息队列 配置系统等 深度集成 为美团点评各业务线提供系统丰富的性能指标 健康状况 实时告警等 spring boot 实践学习案例 是 spring boot 初学者及核心技术巩固的最佳实践 另外写博客 用 OpenWrite Spring Boot基础教程 Spring Boot 2 x版本连载中 帮助 Android App 进行组件化改造的路由框架 提高 Android UI 开发效率的 UI 库时间选择器 省市区三级联动 Luban 鲁班可能是最接近微信朋友圈的图片压缩算法 Gitee 最有价值开源项目 小而全而美的第三方登录开源组件 目前已支持Github Gitee 微博 钉钉 百度 Coding 腾讯云开发者平台 OSChina 支付宝 QQ 微信 淘宝 Google Facebook 抖音 领英 小米 微软 今日头条人人 华为 企业微信 酷家乐 Gitlab 美团 饿了么 推特 飞书 京东 阿里云 喜马拉雅 Amazon Slack和 Line 等第三方平台的授权登录 Login so easy 今日头条屏幕适配方案终极版 一个极低成本的 Android 屏幕适配方案  Banner 2 来了 Android广告图片轮播控件 内部基于ViewPager2实现 Indicator和UI都可以自定义  零代码 热更新 自动化 ORM 库 后端接口和文档零代码 前端 客户端 定制返回 JSON 的数据和结构一个涵盖六个专栏分布式消息队列 分布式事务的仓库 希望胖友小手一抖 右上角来个 Star 感恩 1 24Mybatis通用分页插件OkGo 3 震撼来袭 该库是基于 协议 封装了 OkHttp 的网络请求框架 比 Retrofit 更简单易用 支持 RxJava RxJava2 支持自定义缓存 支持批量断点下载管理和批量上传管理功能含 Flink 入门 概念 原理 实战 性能调优 源码解析等内容 涉及等内容的学习案例 还有 Flink 落地应用的大型项目案例 PVUV 日志存储 百亿数据实时去重 监控告警 分享 欢迎大家支持我的专栏 大数据实时计算引擎 Flink 实战与性能优化 安卓平台上的JavaScript自动化工具  ️一个整合了大量主流开源项目高度可配置化的 Android MVP 快速集成框架 Spring源码阅读大数据入门指南 android 4 4以上沉浸式状态栏和沉浸式导航栏管理 适配横竖屏切换 刘海屏 软键盘弹出等问题 可以修改状态栏字体颜色和导航栏图标颜色 以及不可修改字体颜色手机的适配 适用于一句代码轻松实现 以及对bar的其他设置 详见README 简书请参考 www jianshu com p 2a884e211a62业内为数不多致力于极致体验的超强全自研跨平台 windows android iOS 流媒体内核 通过模块化自由组合 支持实时RTMP推流 RTSP推流 RTMP播放器 RTSP播放器 录像 多路流媒体转发 音视频导播 动态视频合成 音频混音 直播互动 内置轻量级RTSP服务等 比快更快 业界真正靠谱的超低延迟直播SDK 1秒内 低延迟模式下2 4 ms DataX是阿里云DataWorks数据集成的开源版本 mall学习教程 架构 业务 技术要点全方位解析 mall项目 4 k star 是一套电商系统 使用现阶段主流技术实现 涵盖了等技术 采用Docker容器化部署 Android开源弹幕引擎 烈焰弹幕使 ～spring cloud vue oAuth2 全家桶实战 前后端分离模拟商城 完整的购物流程 后端运营平台 可以实现快速搭建企业级微服务项目 支持微信登录等三方登录  一个基于Spring Boot MyBatis的种子项目 用于快速构建中小型API RESTful API项目 强大 可定制 易扩展的 ViewPager 指示器框架 是的最佳替代品 支持角标 更支持在非ViewPager场景下使用 使用hide show 切换Fragment或使用se 27天成为Java大神安卓学习笔记 cim cross IM 适用于开发者的分布式即时通讯系统Android上一个优雅 万能自定义UI 仿iOS 支持垂直 水平方向切换 支持周视图 自定义周起始 性能高效的日历控件 支持热插拔实现的UI定制 支持标记 自定义颜色 农历 自定义月视图各种显示模式等 Canvas绘制 速度快 占用内存低 你真的想不到日历居然还可以如此优雅hsweb haʊs wɛb 是一个基于spring boot 2 x开发 首个使用全响应式编程的企业级后台管理系统基础项目  newbee mall 项目 新蜂商城 是一套电商系统 包括 newbee mall 商城系统及 newbee mall admin 商城后台管理系统 基于 Spring Boot 2 X 及相关技术栈开发 前台商城系统包含首页门户 商品分类 新品上线 首页轮播 商品推荐 商品搜索 商品展示 购物车 订单结算 订单流程 个人订单管理 会员中心 帮助中心等模块 后台管理系统包含数据面板 轮播图管理 商品管理 订单管理 会员管理 分类管理 设置等模块 mall swarm是一套微服务商城系统 采用了等核心技术 同时提供了基于Vue的管理后台方便快速搭建系统 mall swarm在电商业务的基础集成了注册中心 配置中心 监控中心 网关等系统功能 文档齐全 附带全套Spring Cloud教程 阅读是一款可以自定义来源阅读网络内容的工具 为广大网络文学爱好者提供一种方便 快捷舒适的试读体验 Spring Cloud基础教程 持续连载更新中阿里巴巴分布式数据库同步系统 解决中美异地机房  基于谷歌最新AAC架构 MVVM设计模式的一套快速开发库 整合OkRxJava Retrofit Glide等主流模块 满足日常开发需求 使用该框架可以快速开发一个高质量 易维护的Android应用 基于SpringCloud2 1的微服务开发脚手架 整合了等 服务治理方面引入等 让项目开发快速进入业务开发 而不需过多时间花费在架构搭建上 持续更新中基于SOA架构的分布式电商购物商城 前后端分离 前台商城 全家桶 后台管理系统等是 难得一见 的 Jetpack MVVM 最佳实践 在 以简驭繁 的代码中 对 视图控制器 乃至 标准化开发模式 形成正确 深入的理解 V部落 Vue SpringBoot实现的多用户博客管理平台 Android Signature V2 Scheme签名下的新一代渠道包打包神器即时通讯 IM 系统多种编程语言实现 LeetCode 剑指 Offer 第 2 版 程序员面试金典 第 6 版 题解专门为刚开始刷题的同学准备的算法基地 没有最细只有更细 立志用动画将晦涩难懂的算法说的通俗易懂 ansj分词 ict的真正java实现 分词效果速度都超过开源版的ict 中文分词 人名识别 词性标注 用户自定义词典 book 任阅 网络小说阅读器 3D翻页效果 txt pdf epub书籍阅读 Wifi传书 LeetCode刷题记录与面试整理mybatis generator界面工具 让你生成代码更简单更快捷Spring Cloud 学习案例 服务发现 服务治理 链路追踪 服务监控等 XPopup2 版本重磅来袭 2倍以上性能提升 带来可观的动画性能优化和交互细节的提升 功能强大 交互优雅 动画丝滑的通用弹窗 可以替代等组件 自带十几种效果良好的动画 支持完全的UI和动画自定义搜狐视频 sohu tv Redis私有云平台spring boot打造文件文档在线预览项目权限管理系统 预览地址 47 1 4 7 138 login零反射全动态Android插件框架分布式配置管理平台 通用 IM 聊天 UI 组件 已经同时支持 Android iOS RN 手把手教你整合最优雅SSM框架 SpringMVC Spring MyBatis换肤框架 极低的学习成本 极好的用户体验 一行 代码就可以实现换肤 你值得拥有  JVM 底层原理最全知识总结 国内首个Spring Cloud微服务化RBAC的管理平台 核心采用前端采用d2 admin中台框架 记得上边点个star 关注更新tcc transaction是TCC型事务java实现 RecyclerView侧滑菜单 Item拖拽 滑动删除Item 自动加载更多 HeaderView FooterView Item分组黏贴 包含美颜等4 余种实时滤镜相机 可拍照 录像 图片修改springboot 框架与其它组件结合如等安卓选择器类库 包括日期及时间选择器 可用于出生日期 营业时间等 单项选择器 可用于性别 民族 职业 学历 星座等 二三级联动选择器 可用于车牌号 基金定投日期等 城市地址选择器 分省级 地市级及区县级 数字选择器 可用于年龄 身高 体重 温度等 日历选日期择器 可用于酒店及机票预定日期 颜色选择器 文件及目录选择器等 Java工程师面试复习指南 本仓库涵盖大部分Java程序员所需要掌握的核心知识 整合了互联网上的很多优质Java技术文章 力求打造为最完整最实用的Java开发者学习指南 如果对你有帮助 给个star告诉我吧 谢谢  Android MVP 快速开发框架 做国内 示例最全面 注释最详细 使用最简单 代码最严谨 的 Android 开源 UI 框架几行代码快速集成二维码扫描功能MeterSphere 是一站式开源持续测试平台 涵盖测试跟踪 接口测试 性能测试 团队协作等功能 全面兼容 JMeter Postman Swagger 等开源 主流标准 记录各种学习笔记 算法 Java 数据库 并发 下一代Android打包工具 1 个渠道包只需要1 秒钟芋道 mall 商城 基于微服务的思想 构建在 B2C 电商场景下的项目实战 核心技术栈 是 Spring Boot Dubbo 未来 会重构成 Spring Cloud Alibaba Android 万能的等 支持多种Item类型的情况 lanproxy是一个将局域网个人电脑 服务器代理到公网的内网穿透工具 支持tcp流量转发 可支持任何tcp上层协议 访问内网网站 本地支付接口调试 ssh访问 远程桌面 目前市面上提供类似服务的有花生壳 TeamView GoToMyCloud等等 但要使用第三方的公网服务器就必须为第三方付费 并且这些服务都有各种各样的限制 此外 由于数据包会流经第三方 因此对数据安全也是一大隐患 技术交流QQ群 1 6742433 更优雅的驾车体验下载可以很简单 ️ 云阅 一款基于网易云音乐UI 使用玩架构开发的符合Google Material Design的Android客户端开源的 Material Design 豆瓣客户端一款针对系统PopupWindow优化的Popup库 功能强大 支持背景模糊 使用简单 你会爱上他的 PLDroidPlayer 是七牛推出的一款免费的适用于 Android 平台的播放器 SDK 采用全自研的跨平台播放内核 拥有丰富的功能和优异的性能 可高度定制化和二次开发 该项目已停止维护 9 Porn Android 客户端 突破游客每天观看1 次视频的限制 还可以下载视频 ️蓝绿 灰度 路由 限流 熔断 降级 隔离 追踪 流量染色 故障转移一本关于排序算法的 GitBook 在线书籍 十大经典排序算法 多语言实现 多种下拉刷新效果 上拉加载更多 可配置自定义头部广告位完全仿微信的图片选择 并且提供了多种图片加载接口 选择图片后可以旋转 可以裁剪成矩形或圆形 可以配置各种其他的参数SoloPi 自动化测试工具龙果支付系统 roncoo pay 是国内首款开源的互联网支付系统 拥有独立的账户体系 用户体系 支付接入体系 支付交易体系 对账清结算体系 目标是打造一款集成主流支付方式且轻量易用的支付收款系统 满足互联网业务系统打通支付通道实现支付收款和业务资金管理等功能 键盘面板冲突 布局闪动处理方案  咕泡学院实战项目 基于SpringBoot Dubbo构建的电商平台 微服务架构 商城 电商 微服务 高并发 kafka Elasticsearch停车场系统源码 停车场小程序 智能停车 Parking system 功能介绍 ①兼容市面上主流的多家相机 理论上兼容所有硬件 可灵活扩展 ②相机识别后数据自动上传到云端并记录 校验相机唯一id和硬件序列号 防止非法数据录入 ③用户手机查询停车记录详情可自主缴费 支持微信 支付宝 银行接口支付 支持每个停车场指定不同的商户进行收款 支付后出场在免费时间内会自动抬杆 ④支持app上查询附近停车场 导航 可用车位数 停车场费用 优惠券 评分 评论等 可预约车位 ⑤断电断网支持岗亭人员使用app可接管硬件进行停车记录的录入 技术架构 后端开发语言java 框架oauth2 spring 成长路线 但学到不仅仅是Java 业界首个支持渐进式组件化改造的Android组件化开源框架 支持跨进程调用SpringBoot2 从入门到实战 旨在打造在线最佳的 Java 学习笔记 含博客讲解和源码实例 包括 Java SE 和 Java WebJava诊断工具年薪百万互联网架构师课程文档及源码 公开部分 AndroidHttpCapture网络诊断工具 是一款Android手机抓包软件 主要功能包括 手机端抓包 PING DNS TraceRoute诊断 抓包HAR数据上传分享 你也可以看成是Android版的 Fiddler o 这可能是史上功能最全的Java权限认证框架 目前已集成 登录认证 权限认证 分布式Session会话 微服务网关鉴权 单点登录 OAuth2 踢人下线 Redis集成 前后台分离 记住我模式 模拟他人账号 临时身份切换 账号封禁 多账号认证体系 注解式鉴权 路由拦截式鉴权 花式token生成 自动续签 同端互斥登录 会话治理 密码加密 jwt集成 Spring集成 WebFlux集成 Android平台下的富文本解析器 支持Html和Markdown智能图片裁剪框架 自动识别边框 手动调节选区 使用透视变换裁剪并矫正选区 适用于身份证 名片 文档等照片的裁剪 俗名 可垂直跑 可水平跑的跑马灯 学名 可垂直翻 可水平翻的翻页公告 小马哥技术周报 Android Video Player 安卓视频播放器 封装模仿抖音并实现预加载 列表播放 悬浮播放 广告播放 弹幕 重学Java设计模式 是一本互联网真实案例实践书籍 以落地解决方案为核心 从实际业务中抽离出 交易 营销 秒杀 中间件 源码等22个真实场景 来学习设计模式的运用 欢迎关注小傅哥 微信 fustack 公众号 bugstack虫洞栈 博客 bugstack cnmybatis源码中文注释一款开源的GIF在线分享App 乐趣就要和世界分享 MPush开源实时消息推送系统在线云盘 网盘 OneDrive 云存储 私有云 对象存储 h5ai基于Spring Boot 2 x的一站式前后端分离快速开发平台XBoot 微信小程序 Uniapp 前端 Vue iView Admin 后端分布式限流 同步锁 验证码 SnowFlake雪花算法ID 动态权限 数据权限 工作流 代码生成 定时任务 社交账号 短信登录 单点登录 OAuth2开放平台 客服机器人 数据大屏 暗黑模式Guns基于SpringBoot 2 致力于做更简洁的后台管理系统 完美整合项目代码简洁 注释丰富 上手容易 同时Guns包含许多基础模块 用户管理 角色管理 部门管理 字典管理等1 个模块 可以直接作为一个后台管理系统的脚手架  Android 版本更新一个简洁而优雅的Android原生UI框架 解放你的双手 一套完整有效的android组件化方案 支持组件的组件完全隔离 单独调试 集成调试 组件交互 UI跳转 动态加载卸载等功能适用于Java和Android的快速 低内存占用的汉字转拼音库 Codes of my MOOC Course <我在慕课网上的课程 算法与数据结构 示例代码 包括C 和Java版本 课程的更多更新内容及辅助练习也将逐步添加进这个代码仓  Hope Boot 一款现代化的脚手架项目一个简单漂亮的SSM Spring SpringMVC Mybatis 博客系统根据Gson库使用的要求 将JSONObject格式的String 解析成实体B站 哔哩哔哩 Bilibili 自动签到投币工具 每天轻松获取65经验值 支持每日自动投币 银瓜子兑换硬币 领取大会员福利 大会员月底给自己充电等功能 呐 赶快和我一起成为Lv6吧 IJPay 让支付触手可及 封装了微信支付 QQ支付 支付宝支付 京东支付 银联支付 PayPal 支付等常用的支付方式以及各种常用的接口 不依赖任何第三方 mvc 框架 仅仅作为工具使用简单快速完成支付模块的开发 可轻松嵌入到任何系统里 右上角点下小星星  High quality pure Weex demo 网易严选 App 感受 Weex 开发Android 快速实现新手引导层的库 通过简洁链式调用 一行代码实现引导层的显示通过标签直接生成shape 无需再写shape xml 本库是一款基于RxJava2 Retrofit2实现简单易用的网络请求框架 结合android平台特性的网络封装库 采用api链式调用一点到底 集成cookie管理 多种缓存模式 极简https配置 上传下载进度显示 请求错误自动重试 请求携带token 时间戳 签名sign动态配置 自动登录成功后请求重发功能 3种层次的参数设置默认全局局部 默认标准ApiResult同时可以支持自定义的数据结构 已经能满足现在的大部分网络请求 Android BLE蓝牙通信库 基于Flink实现的商品实时推荐系统 flink统计商品热度 放入redis缓存 分析日志信息 将画像标签和实时记录放入Hbase 在用户发起推荐请求后 根据用户画像重排序热度榜 并结合协同过滤和标签两个推荐模块为新生成的榜单的每一个产品添加关联产品 最后返回新的用户列表 播放器基础库 专注于播放视图组件的高复用性和组件间的低耦合 轻松处理复杂业务 图片选择库 单选 多选 拍照 裁剪 压缩 自定义 包括视频选择和录制 DataX集成可视化页面 选择数据源即可一键生成数据同步任务 支持等数据源 批量创建RDBMS数据同步任务 集成开源调度系统 支持分布式 增量同步数据 实时查看运行日志 监控执行器资源 KILL运行进程 数据源信息加密等  Deprecated android 自定义日历控件 支持左右无限滑动 周月切换 标记日期显示 自定义显示效果跳转到指定日期一个通过动态加载本地皮肤包进行换肤的皮肤框架这是RedSpider社区成员原创与维护的Java多线程系列文章 一站式Apache Kafka集群指标监控与运维管控平台快速开发工具类收集 史上最全的开发工具类 欢迎Follow Fork Star后端技术总结 包括Java基础 JVM 数据库 mysql redis 计算机网络 算法 数据结构 操作系统 设计模式 系统设计 框架原理 最佳阅读地址Android源码设计模式分析项目可能是最好的支付SDK 停止维护 组件化综合案例 包含微信新闻 头条视频 美女图片 百度音乐 干活集中营 玩Android 豆瓣读书电影 知乎日报等等模块 架构模式 组件化阿里VLayout 腾讯X5 腾讯bugly 融合开发中需要的各种小案例 开源OA系统 码云GVP Java开源oa 企业OA办公平台 企业OA 协同办公OA 流程平台OA O2OA OA 支持国产麒麟操作系统和国产数据库 达梦 人大金仓 政务OA 军工信息化OA以Spring Cloud Netflix作为服务治理基础 展示基于tcc思想所实现的分布式事务解决方案一个帮助您完成从缩略视图到原视图无缝过渡转变的神奇框架 系统重构与迁移指南 手把手教你分析 评估现有系统 制定重构策略 探索可行重构方案 搭建测试防护网 进行系统架构重构 服务架构重构 模块重构 代码重构 数据库重构 重构后的架构守护版本检测升级 更新 库小说精品屋是一个多平台 web 安卓app 微信小程序 功能完善的屏幕自适应小说漫画连载系统 包含精品小说专区 轻小说专区和漫画专区 包括小说 漫画分类 小说 漫画搜索 小说 漫画排行 完本小说 漫画 小说 漫画评分 小说 漫画在线阅读 小说 漫画书架 小说 漫画阅读记录 小说下载 小说弹幕 小说 漫画自动采集 更新 纠错 小说内容自动分享到微博 邮件自动推广 链接自动推送到百度搜索引擎等功能 Android 徽章控件 致力于打造一款极致体验的 www wanandroid com 客户端 知识和美是可以并存的哦QAQn ≧ ≦ n 从源码层面 剖析挖掘互联网行业主流技术的底层实现原理 为广大开发者 “提升技术深度” 提供便利 目前开放 Spring 全家桶 Mybatis Netty Dubbo 框架 及 Redis Tomcat 中间件等Redis 一站式管理平台 支持集群的监控 安装 管理 告警以及基本的数据操作该项目不再维护 仅供学习参考专注批量推送的小而美的工具 目前支持 模板消息 公众号 模板消息 小程序 微信客服消息 微信企业号 企业微信消息 阿里云短信 阿里大于模板短信 腾讯云短信 云片网短信 E Mail HTTP请求 钉钉 华为云短信 百度云短信 又拍云短信 七牛云短信Android 平台开源天气 App 采用等开源库来实现 SpringBoot 相关漏洞学习资料 利用方法和技巧合集 黑盒安全评估 check listAndroid 权限请求框架 已适配 Android 11微信SDK JAVA 公众平台 开放平台 商户平台 服务商平台  QMQ是去哪儿网内部广泛使用的消息中间件 自2 12年诞生以来在去哪儿网所有业务场景中广泛的应用 包括跟交易息息相关的订单场景 也包括报价搜索等高吞吐量场景  Java 23种设计模式全归纳linux运维监控工具 支持系统信息 内存 cpu 温度 磁盘空间及IO 硬盘smart 系统负载 网络流量 进程等监控 API接口 大屏展示 拓扑图 端口监控 docker监控 日志文件监控 数据可视化 webSSH工具 堡垒机 跳板机 这可能是全网最好用的ViewPager轮播图 简单 高效 一行代码实现循环轮播 一屏三页任意变 指示器样式任你挑 一种简单有效的android组件化方案 支持组件的代码资源隔离 单独调试 集成调试 组件交互 UI跳转 生命周期等完整功能 一个强大 1 % 兼容 支持 AndroidX 支持 Kotlin并且灵活的组件化框架JPress 一个使用 Java 开发的建站神器 目前已经有 1 w 网站使用 JPress 进行驱动 其中包括多个政府机构 2 上市公司 中科院 红 字会等 分布式事务易用的轻量化网络爬虫 Android系统源码分析重构中一款免费的数据可视化工具 报表与大屏设计 类似于excel操作风格 在线拖拽完成报表设计 功能涵盖 报表设计 图形报表 打印设计 大屏设计等 永久免费 秉承“简单 易用 专业”的产品理念 极大的降低报表开发难度 缩短开发周期 节省成本 解决各类报表难题 Android Activity 滑动返回 支持微信滑动返回样式 横屏滑动返回 全屏滑动返回SpringBoot 基础教程 从入门到上瘾 基于2 M5制作 仿微信视频拍摄UI 基于ffmpeg的视频录制编辑Python 1 天从新手到大师 分享 GitHub 上有趣 入门级的开源项目中英文敏感词 语言检测 中外手机 电话归属地 运营商查询 名字推断性别 手机号抽取 身份证抽取 邮箱抽取 中日文人名库 中文缩写库 拆字词典 词汇情感值 停用词 反动词表 暴恐词表 繁简体转换 英文模拟中文发音 汪峰歌词生成器 职业名称词库 同义词库 反义词库 否定词库 汽车品牌词库 汽车零件词库 连续英文切割 各种中文词向量 公司名字大全 古诗词库 IT词库 财经词库 成语词库 地名词库 历史名人词库 诗词词库 医学词库 饮食词库 法律词库 汽车词库 动物词库 中文聊天语料 中文谣言数据 百度中文问答数据集 句子相似度匹配算法集合 bert资源 文本生成 摘要相关工具 cocoNLP信息抽取 2 21年最新总结 阿里 腾讯 百度 美团 头条等技术面试题目 以及答案 专家出题人分析汇总 AiLearning 机器学习 MachineLearning ML 深度学习 DeepLearning DL 自然语言处理 NLP123 6智能刷票 订票结巴中文分词 动手学深度学习 面向中文读者 能运行 可讨论 中英文版被全球175所大学采用教学 中文分词 词性标注 命名实体识别 依存句法分析 语义依存分析 新词发现 关键词短语提取 自动摘要 文本分类聚类 拼音简繁转换 自然语言处理微信个人号接口 微信机器人及命令行微信 三十行即可自定义个人号机器人 数据结构和算法必知必会的5 个代码实现JumpServer 是全球首款开源的堡垒机 是符合 4A 的专业运维安全审计系统 飞桨 核心框架 深度学习 机器学习高性能单机 分布式训练和跨平台部署 中国程序员容易发音错误的单词微信 跳一跳 Python 辅助 python模拟登陆一些大型网站 还有一些简单的爬虫 希望对你们有所帮助 ️ 如果喜欢记得给个star哦  网络爬虫实战 淘宝 京东 网易云 B站 123 6 抖音 笔趣阁 漫画小说下载 音乐电影下载等Python爬虫代理IP池 proxy pool wtfpython的中文翻译 施工结束 能力有限 欢迎帮我改进翻译提供多款 Shadowrocket 规则 带广告过滤功能 用于 iOS 未越狱设备选择性地自动翻墙  123 6 购票助手 支持集群 多账号 多任务购票以及 Web 页面管理 walle 瓦力 Devops开源项目代码部署平台一些非常有趣的python爬虫例子 对新手比较友好 主要爬取淘宝 天猫 微信 豆瓣 QQ等网站机器学习相关教程1 Chinese Word Vectors 上百种预训练中文词向量 网易云音乐命令行版本一款入门级的人脸 视频 文字检测以及识别的项目  编程随想 整理的 太子党关系网络 专门揭露赵国的权贵微信助手 1 每日定时给好友 女友 发送定制消息 2 机器人自动回复好友 3 群助手功能 例如 查询垃圾分类 天气 日历 电影实时票房 快递物流 PM2 5等 二维码生成器 支持 gif 动态图片二维码 阿布量化交易系统 股票 期权 期货 比特币 机器学习 基于python的开源量化交易 量化投资架构 book 中华新华字典数据库 包括歇后语 成语 词语 汉字  Git AWS Google 镜像 SS SSR VMESS节点行业研究报告的知识储备库中文翻译手写实现李航 统计学习方法 书中全部算法 Python 抖音机器人 论如何在抖音上找到漂亮小姐姐？ 迁移学习python爬虫教程系列 从 到1学习python爬虫 包括浏览器抓包 手机APP抓包 如 fiddler mitmproxy 各种爬虫涉及的模块的使用 如等 以及IP代理 验证码识别 Mysql MongoDB数据库的python使用 多线程多进程爬虫的使用 css 爬虫加密逆向破解 JS爬虫逆向 分布式爬虫 爬虫项目实战实例等Python脚本 模拟登录知乎 爬虫 操作excel 微信公众号 远程开机越来越多的网站具有反爬虫特性 有的用图片隐藏关键数据 有的使用反人类的验证码 建立反反爬虫的代码仓库 通过与不同特性的网站做斗争 无恶意 提高技术 欢迎提交难以采集的网站 因工作原因 项目暂停  人人影视bot 完全对接人人影视全部无删减资源莫烦Python 中文AI教学飞桨 官方模型库 包含多种学术前沿和工业场景验证的深度学习模型 轻量级人脸检测模型 百度云 百度网盘Python客户端 Python进阶 Intermediate Python 中文版 提供同花顺客户端 国金 华泰客户端 雪球的基金 股票自动程序化交易以及自动打新 支持跟踪 joinquant ricequant 模拟交易 和 实盘雪球组合 量化交易组件QUANTAXIS 支持任务调度 分布式部署的 股票 期货 期权 港股 虚拟货币 数据 回测 模拟 交易 可视化 多账户 纯本地量化解决方案INFO SPIDER 是一个集众多数据源于一身的爬虫工具箱 旨在安全快捷的帮助用户拿回自己的数据 工具代码开源 流程透明 支持数据源包括GitHub QQ邮箱 网易邮箱 阿里邮箱 新浪邮箱 Hotmail邮箱 Outlook邮箱 京东 淘宝 支付宝 中国移动 中国联通 中国电信 知乎 哔哩哔哩 网易云音乐 QQ好友 QQ群 生成朋友圈相册 浏览器浏览历史 123 6 博客园 CSDN博客 开源中国博客 简书 中文BERT wwm系列模型 Python入门网络爬虫之精华版中文 iOS Mac 开发博客列表Python网页微信APIpkuseg多领域中文分词工具自己动手做聊天机器人教程基于搜狗微信搜索的微信公众号爬虫接口用深度学习对对联 v2ray xray多用户管理部署程序各种脚本 关于 虾米 xiami com 百度网盘 pan baidu com 115网盘 115 com 网易音乐 music 163 com 百度音乐 music baidu com 36 网盘 云盘 yunpan cn 视频解析 flvxz com bt torrent ↔ magnet ed2k 搜索 tumblr 图片下载 unzip查看被删的微信好友定投改变命运 让时间陪你慢慢变富 onregularinvesting com 机器学习实战 Python3 kNN 决策树 贝叶斯 逻辑回归 SVM 线性回归 树回归Statistical learning methods 统计学习方法 第2版 李航 笔记 代码 notebook 参考文献 Errata lihang stock 股票系统 使用python进行开发 基于深度学习的中文语音识别系统京东抢购助手 包含登录 查询商品库存 价格 添加 清空购物车 抢购商品 下单 查询订单等功能莫烦Python 中文AI教学机器学习算法python实现新浪微博爬虫 用python爬取新浪微博数据的算法以及通用生成对抗网络图像生成的理论与实践研究 青岛大学开源 Online Judge QQ群 49671 125 admin qduoj comWeRoBot 是一个微信公众号开发框架 基于Django的博客系统 中文近义词 聊天机器人 智能问答工具包开源财经数据接口库巡风是一款适用于企业内网的漏洞快速应急 巡航扫描系统  番号大全 解决电脑 手机看电视直播的苦恼 收集各种直播源 电视直播网站知识图谱构建 自动问答 基...',
            'fork' => false,
            'url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship',
            'forks_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/forks',
            'keys_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/keys{/key_id}',
            'collaborators_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/collaborators{/collaborator}',
            'teams_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/teams',
            'hooks_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/hooks',
            'issue_events_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/issues/events{/number}',
            'events_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/events',
            'assignees_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/assignees{/user}',
            'branches_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/branches{/branch}',
            'tags_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/tags',
            'blobs_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/git/blobs{/sha}',
            'git_tags_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/git/tags{/sha}',
            'git_refs_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/git/refs{/sha}',
            'trees_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/git/trees{/sha}',
            'statuses_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/statuses/{sha}',
            'languages_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/languages',
            'stargazers_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/stargazers',
            'contributors_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/contributors',
            'subscribers_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/subscribers',
            'subscription_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/subscription',
            'commits_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/commits{/sha}',
            'git_commits_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/git/commits{/sha}',
            'comments_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/comments{/number}',
            'issue_comment_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/issues/comments{/number}',
            'contents_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/contents/{+path}',
            'compare_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/compare/{base}...{head}',
            'merges_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/merges',
            'archive_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/{archive_format}{/ref}',
            'downloads_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/downloads',
            'issues_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/issues{/number}',
            'pulls_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/pulls{/number}',
            'milestones_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/milestones{/number}',
            'notifications_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/notifications{?since,all,participating}',
            'labels_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/labels{/name}',
            'releases_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/releases{/id}',
            'deployments_url' => 'https://api.github.com/repos/cirosantilli/china-dictatorship/deployments',
            'created_at' => '2015-04-02T20:51:50Z',
            'updated_at' => '2022-09-12T06:00:42Z',
            'pushed_at' => '2022-09-11T15:24:03Z',
            'git_url' => 'git://github.com/cirosantilli/china-dictatorship.git',
            'ssh_url' => 'git@github.com:cirosantilli/china-dictatorship.git',
            'clone_url' => 'https://github.com/cirosantilli/china-dictatorship.git',
            'svn_url' => 'https://github.com/cirosantilli/china-dictatorship',
            'homepage' => 'https://cirosantilli.com/china-dictatorship',
            'size' => 65111,
            'stargazers_count' => 993,
            'watchers_count' => 993,
            'language' => 'HTML',
            'has_issues' => true,
            'has_projects' => true,
            'has_downloads' => true,
            'has_wiki' => true,
            'has_pages' => true,
            'forks_count' => 195,
            'archived' => false,
            'disabled' => false,
            'open_issues_count' => 521,
            'license' =>
                [
                    'key' => 'cc-by-sa-4.0',
                    'name' => 'Creative Commons Attribution Share Alike 4.0 International',
                    'spdx_id' => 'CC-BY-SA-4.0',
                    'url' => 'https://api.github.com/licenses/cc-by-sa-4.0',
                    'node_id' => 'MDc6TGljZW5zZTI2',
                ],
            'allow_forking' => true,
            'is_template' => true,
            'web_commit_signoff_required' => false,
            'topics' =>
                [
                    0 => '996',
                    1 => 'censorship',
                    2 => 'censorship-circumvention',
                    3 => 'china',
                    4 => 'china-dictatorship',
                    5 => 'chinese-communist-party',
                    6 => 'covid-19',
                    7 => 'covid-19-china',
                    8 => 'dictator',
                    9 => 'dictatorship',
                    10 => 'falun-gong',
                    11 => 'gfw',
                    12 => 'great-firewall',
                    13 => 'human-rights',
                    14 => 'shadowsocks',
                    15 => 'socks5',
                    16 => 'tiananmen',
                    17 => 'totalitarian',
                    18 => 'xi-jinping',
                    19 => 'xinjiang',
                ],
            'visibility' => 'public',
            'forks' => 195,
            'open_issues' => 521,
            'watchers' => 993,
            'default_branch' => 'master',
            'permissions' =>
                [
                    'admin' => false,
                    'maintain' => false,
                    'push' => false,
                    'triage' => false,
                    'pull' => true,
                ],
            'score' => 1.0,
            'github_repository_id' => 33331247,
        ];
        foreach($attributes as $attribute => $value){
            $this->validateAttribute($model, $attribute, $value);
        }
        $model->fill($attributes);
        $model->validate();
        $repositories = GithubRepository::search('laravel oauth2');
        $names = $repositories->pluck('name');
		$this->assertGreaterThan(27, $repositories->count());
	}

    /**
     * @param GithubRepository $model
     * @param string $attribute
     * @param $value
     * @return void
     */
    private function validateAttribute(GithubRepository $model, string $attribute, $value): void
    {
        $p = $model->getPropertyModel($attribute);
        $class = get_class($p);
        $this->assertEquals($p->getPHPType(), gettype($value),
            ": $attribute is not of " . $class . " type " . $p->getPHPType() . "! Got: " . var_export
            ($value, true));
        $this->validateMySQLType($p, $class);
        $this->validateLengthForDB($value, $p, $attribute);
    }

    /**
     * @param \App\Properties\BaseProperty|null $p
     * @param string $class
     * @return void
     */
    private function validateMySQLType(?\App\Properties\BaseProperty $p, string $class): void
    {
        if ($p->getPHPType() == 'array') {
            $expectedMySQLType = MySQLTypes::getType('text');
            $this->assertEquals($expectedMySQLType, $p->getDBType(),
                "$class is an array so should have '$expectedMySQLType' mysql type not this: " . $p->getDBType());
        }
    }

    /**
     * @param $value
     * @param \App\Properties\BaseProperty|null $p
     * @param string $attribute
     * @return void
     */
    private function validateLengthForDB($value, ?\App\Properties\BaseProperty $p, string $attribute): void
    {
        $str = QMStr::jsonEncodeIfNecessary($value);
        $DBColumn = $p->getDBColumn();
        $maxColumnLength = $DBColumn->getMaxLength();
        $class = get_class($p);
        if($maxColumnLength === null){
            if(gettype($value) === PhpTypes::STRING){
                $this->fail("No max length set for $class of type " . gettype($value) . " with value: " . $str);
            }
            if(gettype($value) === PhpTypes::ARRAY){
                $p->getMaxLength();
                $this->fail("No max length set for $class of type " . gettype($value) . " with value: " . $str);
            }
            return;
        }
        $actualLength = strlen($str);
        if ($actualLength > $maxColumnLength) {
            $path = $DBColumn->createMigration();
            $this->fail("Value for '$attribute'' is too long! $actualLength > $maxColumnLength. Run this migration:
                $path
                ");
        }
    }

}

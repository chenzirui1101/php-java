<template>
  <footer class="site-footer">
    <div class="container">
      <div class="row">
        <div class="col-12">
          <!-- 免责声明 -->
          <div class="disclaimer-section">
            <h3 class="disclaimer-title">【免责声明】</h3>
            <ol class="disclaimer-list">
              <li>本分享的学习资料仅供个人学习、研究使用，禁止用于任何商业盈利活动；</li>
              <li>所有资料均来自互联网公开渠道，其版权归原作者或出版社所有；</li>
              <li>分享者仅提供信息存储空间，不代表认同其观点或内容的合法性；</li>
              <li>使用者应在下载后24小时内自行删除，支持购买正版；</li>
              <li>如涉及版权问题，请及时联系删除。</li>
            </ol>
          </div>
          
          <!-- 使用提醒 -->
          <div class="reminder-section">
            <h3 class="reminder-title">【使用提醒】</h3>
            <ul class="reminder-list">
              <li>请遵守相关法律法规和平台规则</li>
              <li>尊重知识产权，支持正版资源</li>
              <li>谨慎使用，后果自负</li>
            </ul>
          </div>
          
          <!-- 链接 -->
          <div class="links-section">
            <router-link to="/more/about" class="footer-link">关于我们</router-link>
            <span class="link-divider">|</span>
            <router-link to="/more/contact" class="footer-link">联系我们</router-link>
          </div>
          
          <!-- 版权 -->
          <p class="copyright">© 2026 潮青行. All rights reserved.</p>
          <p class="copyright" style="margin-top: 6px;">
            <a href="https://beian.miit.gov.cn/" target="_blank" rel="noopener noreferrer" style="color: rgba(255,255,255,0.8); text-decoration: none;">粤ICP备2026046534号-1</a>
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>

<script setup>
</script>

<style scoped>
.site-footer {
  background: linear-gradient(135deg, rgba(44, 62, 80, 0.7), rgba(52, 73, 94, 0.7));
  color: white;
  padding: 40px 0;
  margin-top: auto;
  box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
}

.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 15px;
}

.row {
  display: flex;
  justify-content: center;
}

.col-12 {
  width: 100%;
  text-align: center;
}

.disclaimer-section,
.reminder-section {
  margin-bottom: 24px;
}

.disclaimer-title,
.reminder-title {
  font-size: 1.1rem;
  font-weight: bold;
  color: #FFD93D;
  margin-bottom: 12px;
}

.disclaimer-list {
  text-align: left;
  max-width: 600px;
  margin: 0 auto 20px;
  padding-left: 20px;
  line-height: 1.8;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
}

.disclaimer-list li {
  margin-bottom: 6px;
}

.reminder-list {
  text-align: left;
  max-width: 600px;
  margin: 0 auto;
  padding-left: 20px;
  line-height: 1.8;
  font-size: 0.95rem;
  color: rgba(255, 255, 255, 0.9);
  list-style-type: none;
}

.reminder-list li {
  margin-bottom: 6px;
}

.reminder-list li::before {
  content: "• ";
  color: #FFD93D;
}

.links-section {
  margin-bottom: 20px;
}

.footer-link {
  color: #FFD93D;
  text-decoration: none;
  font-size: 1rem;
  transition: all 0.3s ease;
  padding: 0 12px;
}

.footer-link:hover {
  color: #fff;
  text-decoration: underline;
}

.link-divider {
  color: rgba(255, 255, 255, 0.5);
}

.copyright {
  font-size: 0.875rem;
  color: rgba(255, 255, 255, 0.8);
  margin: 0;
}

@media (max-width: 768px) {
  .site-footer {
    padding: 30px 0;
  }
  
  .disclaimer-list,
  .reminder-list {
    font-size: 0.85rem;
    padding-left: 15px;
  }
  
  .disclaimer-title,
  .reminder-title {
    font-size: 1rem;
  }
  
  .footer-link {
    font-size: 0.9rem;
    padding: 0 8px;
  }
}
</style>
const { Sequelize } = require('sequelize');

const isDevelopment = process.env.NODE_ENV === 'development';
const commonOptions = {
  logging: isDevelopment ? console.log : false,
  define: {
    timestamps: true,
    underscored: true,
    createdAt: 'created_at',
    updatedAt: 'updated_at'
  }
};

// SSL 配置（审计 M1）：提供 CA 证书时强制校验；未提供时降级运行但生产环境告警。
// 推荐生产环境设置 DATABASE_SSL_CA 为 CA 证书内容（PEM）。
function buildSslOptions() {
  const sslCa = process.env.DATABASE_SSL_CA;
  if (sslCa) {
    // 有 CA 证书 → 启用严格校验，防中间人攻击
    return { ssl: { require: true, rejectUnauthorized: true, ca: sslCa } };
  }
  // 无 CA 证书：开发/受信内网可降级，生产环境打印警告
  if (!isDevelopment && process.env.NODE_ENV === 'production') {
    console.warn('[WARN] DATABASE_SSL_CA 未配置，数据库 SSL 关闭证书校验（存在中间人攻击风险）。生产环境请配置 CA 证书。');
  }
  return { ssl: { require: true, rejectUnauthorized: false } };
}

const sequelize = process.env.DATABASE_URL
  ? new Sequelize(process.env.DATABASE_URL, {
      ...commonOptions,
      dialect: 'postgres',
      dialectOptions: buildSslOptions()
    })
  : new Sequelize({
      ...commonOptions,
      dialect: 'sqlite',
      storage: process.env.DB_STORAGE || 'database.sqlite'
    });

module.exports = sequelize;

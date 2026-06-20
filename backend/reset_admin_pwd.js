require('dotenv').config();
const { User } = require('./models');
const bcrypt = require('bcryptjs');

(async () => {
    try {
        const password = process.env.DEFAULT_ADMIN_PASSWORD || 'Admin@123';
        const hashedPassword = await bcrypt.hash(password, 10);

        // Try to find by ID 1 first, then by username 'admin'
        let admin = await User.findByPk(1);
        if (!admin) {
            admin = await User.findOne({ where: { username: 'admin' } });
        }

        if (admin) {
            await admin.update({ password: hashedPassword });
            // 安全：不再打印明文密码到日志（审计 C3）
            console.log(`SUCCESS: Password for user '${admin.username}' (ID: ${admin.id}) has been reset.`);
            console.log('       请通过 DEFAULT_ADMIN_PASSWORD 环境变量查看新密码。');
        } else {
            console.log('Admin user not found. Creating one...');
            await User.create({
                id: 1,
                username: 'admin',
                email: 'admin@example.com',
                password: hashedPassword,
                role: 'admin',
                status: 'active'
            });
            console.log('SUCCESS: Admin user created.');
            console.log('       新密码来自 DEFAULT_ADMIN_PASSWORD 环境变量，请查阅该变量。');
        }
    } catch (e) {
        console.error('Error resetting password:', e);
    } finally {
        process.exit();
    }
})();

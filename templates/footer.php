        </div>
    </main>

    <?php if (isset($extraScripts) && is_array($extraScripts)): ?>
        <?php foreach ($extraScripts as $scriptPath): ?>
            <script src="<?php echo e(base_url($scriptPath)); ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>

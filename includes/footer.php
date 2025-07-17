        </div> <!-- Cierre del div.container que abrimos en header.php -->
        <footer class="site-footer">
            <div class="footer-content">
                <p>Secretaría Municipal de Salud - Alcaldía de Pasto &copy; <?php echo date('Y'); ?> Todos los derechos reservados</p>
                
                <nav class="footer-nav">
                    <a href="../vistas/terminos.php">Términos de Servicio</a> |
                    <a href="../vistas/privacidad.php">Política de Privacidad</a> |
                    <a href="../vistas/contacto.php">Contacto</a>
                </nav>
                
                <p class="version">Versión 1.0.0</p>
            </div>
        </footer>

        <!-- Scripts globales -->
        <script src="../assets/js/main.js"></script>
        
        <!-- Script específico para la página -->
        <?php if (isset($page_script)): ?>
            <script src="../assets/js/<?php echo $page_script; ?>"></script>
        <?php endif; ?>
    </body>
</html>
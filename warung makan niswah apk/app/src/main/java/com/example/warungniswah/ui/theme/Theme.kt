package com.example.warungniswah.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val LightColorScheme = lightColorScheme(
    primary = OrangePrimary,
    onPrimary = Color.White,
    primaryContainer = OrangeLight,
    onPrimaryContainer = OrangePrimaryDark,
    secondary = AmberSecondary,
    onSecondary = Color.White,
    secondaryContainer = AmberLight,
    onSecondaryContainer = OrangePrimaryDark,
    background = BackgroundWarm,
    onBackground = NeutralDark,
    surface = SurfaceCard,
    onSurface = NeutralDark,
    surfaceVariant = SurfaceElevated,
    onSurfaceVariant = NeutralMedium,
    outline = NeutralBorder
)

@Composable
fun WarungMakanNiswahTheme(
    content: @Composable () -> Unit
) {
    MaterialTheme(
        colorScheme = LightColorScheme,
        typography = Typography,
        content = content
    )
}

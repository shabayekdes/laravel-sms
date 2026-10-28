# Laravel SMS brand guide

<img src="logo.svg" alt="Laravel SMS" height="64">

This folder holds the logo, symbol and social preview for the `shabayek/laravel-sms` package. Use these files as they are. Don't redraw them.

## Concept

The symbol is called **Echo**. It's two speech bubbles of the same shape, each with a sharp tail at the bottom left. In the front bubble, a dot sends out two arcs of signal: that's the message going out through the gateway. The light-blue bubble behind it is the echo: the same message reaching another phone, another gateway, or coming back as a reply. A clear gap is cut around the front bubble, so the two shapes never touch, in colour or in one colour. Everything sits on a 64-unit grid.

The wordmark sets "Laravel" in SemiBold and "SMS" in Bold. The second colour on "SMS" names what the package does.

## Files

| File | Use |
| --- | --- |
| `logo.svg` | Primary logo for light backgrounds |
| `logo-dark.svg` | Primary logo for dark backgrounds |
| `logo-mono.svg` | One colour (Midnight Navy) for print, embossing, or places where colour isn't available |
| `logo-mono-white.svg` | One colour (white) for dark or photographic backgrounds |
| `symbol.svg` | Symbol alone: avatars, favicons, small spaces |
| `symbol-mono.svg`, `symbol-mono-white.svg` | Symbol in one colour: the gap separates the two bubbles and the signal is cut out |
| `symbol-512.png` | 512×512 raster symbol for places that don't accept SVG |
| `social-preview.svg` | Editable source of the GitHub social preview (live text) |
| `social-preview.png` | 1280×640 social preview to upload to GitHub |

The wordmark in the logo files is converted to outlines, so it looks the same on every system. All SVGs have a `<title>` and `role="img"`.

## Colour

| Token | Hex | Role |
| --- | --- | --- |
| `--lsms-navy` | `#101828` | Text on light, dark background |
| `--lsms-blue` | `#2563EB` | Symbol, primary accent, links on light |
| `--lsms-blue-light` | `#60A5FA` | Echo bubble in the symbol, accent and links on dark |
| `--lsms-mint` | `#2DD4BF` | Sparse highlight on dark only (prompt, status dot) |
| `--lsms-surface` | `#F8FAFC` | Light background |
| `--lsms-white` | `#FFFFFF` | Text on dark, signal inside the symbol |
| `--lsms-surface-dark-raised` | `#1D2939` | Code blocks and cards on dark |
| `--lsms-muted-dark` | `#94A3B8` | Secondary text on dark |

Semantic mapping:

| Role | Light | Dark |
| --- | --- | --- |
| Background | `#F8FAFC` | `#101828` |
| Text | `#101828` | `#FFFFFF` / `#E2E8F0` |
| Muted text | `#475467` | `#94A3B8` |
| Accent / link | `#2563EB` | `#60A5FA` |
| Symbol, front bubble | `#2563EB` | `#2563EB` |
| Symbol, echo bubble | `#60A5FA` | `#60A5FA` |

Measured contrast (WCAG 2.x):

| Pair | Ratio | |
| --- | --- | --- |
| Navy on Surface | 16.96 : 1 | AAA |
| Blue on White | 5.17 : 1 | AA text |
| Blue on Surface | 4.94 : 1 | AA text |
| Light Blue on Navy | 6.98 : 1 | AA text |
| `#94A3B8` on Navy | 6.92 : 1 | AA text |
| Mint on Navy | 9.53 : 1 | AAA |
| Blue on Navy | 3.43 : 1 | Graphics only (≥ 3 : 1), never body text |

The symbol keeps the same colours on light and dark backgrounds. On dark backgrounds, text accents switch to Light Blue.

The echo bubble is a supporting shape. It is 2.43 : 1 on Surface, below the 3 : 1 that WCAG asks of meaningful graphics. That's fine here because the front bubble alone carries the symbol, at 4.94 : 1 on Surface and 3.43 : 1 on Navy.

Don't use mint on light backgrounds. It is too light to read there.

## Typography

- **Wordmark:** Inter style. The outlines were drawn from Adwaita Sans, an SIL OFL fork of Inter. "Laravel" is set in SemiBold 600 and "SMS" in Bold 700, both at 34 units with slightly tight tracking.
- **Documentation and UI:** use the system font stack, so readers don't download any fonts:
  `-apple-system, BlinkMacSystemFont, "Segoe UI", Inter, Roboto, "Helvetica Neue", Arial, sans-serif`
- **Code:** `ui-monospace, SFMono-Regular, "JetBrains Mono", Menlo, Consolas, monospace`

## Spacing and size

- **Clear space:** keep at least ¼ of the symbol's height empty around the logo. At the 64-unit master size, that is 16 units.
- **Lockup:** the gap between the symbol and the wordmark is 14 units. Don't change it.
- **Minimum size:** the full logo should be at least 24 px tall on screen. Below that, use `symbol.svg` alone. The symbol is clearest from 24 px up. At 16 px both bubbles still show, but the signal arcs blur together.

## Usage

Do:

- Use `logo.svg` on light backgrounds and `logo-dark.svg` on dark ones. In a README, pick the right one with `<picture>` and `prefers-color-scheme`.
- Use the mono files when you can only print one colour.
- Scale the files evenly.

Don't:

- Recolour the symbol with colours outside this palette, or add gradients, shadows or outlines.
- Stretch or rotate the logo, or change the gap between the symbol and the wordmark.
- Put the colour logo on busy or mid-tone backgrounds. Use a mono version there.
- Combine the symbol with the official Laravel logo, or use either in a way that suggests an official Laravel product. This is a community package: Laravel is a trademark of Taylor Otwell, and this package is not affiliated with or endorsed by Laravel.

## Regenerating PNGs

The PNGs are rendered from the SVGs with Inkscape and ImageMagick:

```bash
inkscape assets/branding/social-preview.svg -w 1280 -h 640 -o /tmp/social.png
magick /tmp/social.png -strip -define png:compression-level=9 assets/branding/social-preview.png

inkscape assets/branding/symbol.svg -w 512 -h 512 -o /tmp/symbol.png
magick /tmp/symbol.png -strip -define png:compression-level=9 assets/branding/symbol-512.png
```

`social-preview.svg` uses live text in Adwaita Sans/Inter and JetBrains Mono. Install those fonts before you render it, or the system will fall back to other fonts.

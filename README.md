# EffectBlock-HiroTeam

EffectBlock-HiroTeam applies configured potion effects when a player walks over selected blocks. The plugin was created by HiroTeam. This fork keeps the HiroTeam name and the existing Apache-2.0 license.

En français, le plugin applique les effets configurés aux joueurs qui marchent sur les blocs choisis. Le nom HiroTeam et la licence Apache-2.0 sont conservés.

## Requirements and compatibility

- Requires Axolotl-PM API 5.0.0 and the PHP version required by that server. The exact source revisions used for automated verification are pinned in the [build workflow](.github/workflows/build.yml). Compatibility with every intermediate 5.x release has not been established.
- No external plugin dependency is required.
- Existing numeric block ID and metadata rules are converted with Axolotl-PM's block state upgrader. A legacy ID that cannot be mapped is skipped with a warning.

## Installation

Download the verified PHAR from [GitHub Releases](https://github.com/NhanAZ-Plugins/EffectBlock-HiroTeam/releases), copy it into your server's `plugins` directory, and restart the server. The default `config.yml` is copied to the plugin's data directory on first startup.

## Configuration

Each rule has a unique name, a legacy block ID and metadata pair, an effect ID, a duration in seconds, an amplifier, and a visibility setting. The default configuration includes stone and sand.

```yaml
Stone:
  block: "1:0"
  effect: "19:20:0"
  visible: "true"
```

In this example, `1:0` is the legacy block ID and metadata. The effect value `19:20:0` means effect ID 19 for 20 seconds with amplifier 0, which is level I. `visible` controls particles. Use `true` or `false`. Changes take effect after restart.

Rules with malformed values, unknown effect IDs, unsupported legacy blocks, or durations outside the supported range are ignored and logged. Valid rules continue to work. A configured effect is applied when the destination position is above a matching block. The plugin has no commands or permissions.

## Build and verification

The [workflow](.github/workflows/build.yml) tests event behavior, runs PHPStan at maximum level, and uses [DevTools](https://github.com/NhanAZ/DevTools) to build and inspect a PHAR. The workflow pins its tools and server source to reviewed revisions and provides a downloadable build artifact. A new stable release is published only after the release checks pass.

To run the event fixture locally, install the Axolotl-PM dependencies and run `php tests/block-effect.php /path/to/Axolotl-PM`. The isolated server test confirms startup and rule loading. A connected player walking over the configured blocks has not been tested in this maintenance campaign.

## Credits and license

HiroTeam created the original [EffectBlock-HiroTeam](https://github.com/HiroshimaTeam/EffectBlock-HiroTeam), including its bilingual documentation and community links. The original README remains in Git history for the historical video, images, and hosting partnership information. NhanAZ updated this fork in 2026 for Axolotl-PM API 5, with modification notices in the source and manifest. The [Apache License 2.0](LICENSE) text is retained. Report current issues in [GitHub Issues](https://github.com/NhanAZ-Plugins/EffectBlock-HiroTeam/issues).

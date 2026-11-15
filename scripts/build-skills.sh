#!/usr/bin/env bash
# Copies the docs into the Claude skill's references and the ChatGPT bundle, so the
# skills never drift from docs/. Run after changing any doc, then commit the result.
set -euo pipefail
root="$(cd "$(dirname "$0")/.." && pwd)"
ref="$root/skills/claude/tall-ui-kit/references"
gpt="$root/skills/chatgpt"
mkdir -p "$ref" "$gpt/skill/tall-ui-kit/references" "$gpt/custom-gpt/knowledge"

copy() { # source, name
    cp "$root/docs/$1" "$ref/$2"
    cp "$root/docs/$1" "$gpt/skill/tall-ui-kit/references/$2"
}

copy rules.md rules.md
copy tokens.md tokens.md
copy theming.md theming.md
copy javascript.md javascript.md
copy accessibility.md accessibility.md
copy performance.md performance.md
copy motion.md motion.md
copy patterns.md patterns.md
copy components/README.md components.md
copy components/forms.md forms.md
copy components/html-elements.md html-elements.md
copy components/navigation.md navigation.md
copy components/overlays.md overlays.md
copy components/feedback.md feedback.md
copy components/data-display.md data-display.md
copy components/data-table.md data-table.md
copy components/exports.md exports.md
copy components/copy-and-icons.md copy-and-icons.md
copy components/workflow.md workflow.md

# Links between docs point at docs/ paths; inside the skill every file sits in one folder.
for dir in "$ref" "$gpt/skill/tall-ui-kit/references"; do
    sed -i.bak -E 's#\]\(\.\./([a-z-]+)\.md\)#](\1.md)#g; s#\]\(components/README\.md\)#](components.md)#g; s#\]\(components/([a-z-]+)\.md\)#](\1.md)#g; s#\]\(README\.md\)#](components.md)#g' "$dir"/*.md
    rm -f "$dir"/*.bak
done

# The ChatGPT skill shares the Claude SKILL.md body.
cp "$root/skills/claude/tall-ui-kit/SKILL.md" "$gpt/skill/tall-ui-kit/SKILL.md"

# Custom GPT knowledge: at most 20 files, so the docs are merged into a few by family.
k="$gpt/custom-gpt/knowledge"
cat "$ref/rules.md" "$ref/tokens.md" "$ref/theming.md" > "$k/01-rules-tokens-theming.md"
cat "$ref/components.md" "$ref/patterns.md" > "$k/02-components-and-patterns.md"
cat "$ref/forms.md" "$ref/html-elements.md" > "$k/03-forms-and-html-elements.md"
cat "$ref/navigation.md" "$ref/overlays.md" "$ref/feedback.md" "$ref/workflow.md" > "$k/04-navigation-overlays-feedback.md"
cat "$ref/data-display.md" "$ref/data-table.md" "$ref/exports.md" > "$k/05-cards-charts-tables-exports.md"
cat "$ref/accessibility.md" "$ref/performance.md" "$ref/motion.md" "$ref/javascript.md" "$ref/copy-and-icons.md" > "$k/06-accessibility-performance-motion-js-icons.md"

# Zip the ChatGPT skill folder (one top-level folder with SKILL.md, as OpenAI's skills docs describe).
(cd "$gpt/skill" && rm -f ../tall-ui-kit-skill.zip && zip -qr ../tall-ui-kit-skill.zip tall-ui-kit)
echo "Skills rebuilt from docs/."

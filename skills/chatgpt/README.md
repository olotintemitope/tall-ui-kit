# ChatGPT: TALL UI kit skill

Two ways to give ChatGPT the kit. Both are built from `docs/` by `scripts/build-skills.sh`; rerun it after any doc change.

## 1. Skill folder (preferred)

`skill/tall-ui-kit/` is an Agent Skill in the format OpenAI documents:

- `SKILL.md` with `name` and `description` frontmatter (both required), then the instructions.
- `references/` with the component docs (optional folder).
- `agents/openai.yaml` with UI metadata (`interface.display_name`, `short_description`, `default_prompt`) and `policy.allow_implicit_invocation`.

`tall-ui-kit-skill.zip` is the same folder zipped, as one top-level folder with exactly one `SKILL.md`.

Where it works, per OpenAI's "Build skills" page: standalone skills are available in the **ChatGPT desktop app**, the **Codex CLI** and the **IDE extension**; on ChatGPT web and mobile, skills arrive bundled in plugins.

- Codex CLI or IDE: copy or symlink the folder to `~/.agents/skills/tall-ui-kit` (user level) or `<repo>/.agents/skills/tall-ui-kit` (one repository). Invoke it with `$tall-ui-kit` or `/skills`, or let Codex pick it from the description.
- ChatGPT app: add the skill from the app's Skills page (upload `tall-ui-kit-skill.zip` or the folder). OpenAI's help article "Skills in ChatGPT" describes the upload steps and which plans can upload; check it in your account before relying on it (I could not open that page to confirm the steps, see Sources).

## 2. Custom GPT (fallback for any paid plan)

1. ChatGPT, Explore GPTs, Create, Configure.
2. Name: TALL UI kit. Description: Builds Livewire pages with the private x-ui kit.
3. Instructions: paste `custom-gpt/instructions.md`.
4. Knowledge: upload the six files in `custom-gpt/knowledge/` (merged by family to stay well under the knowledge-file limit).
5. Capabilities: Code Interpreter is optional; web browsing off (the kit is private).
6. Share: Only me (the kit is proprietary).

## Sources (checked 2026-11-15)

- Build skills (SKILL.md fields, optional folders, `agents/openai.yaml`, locations, where standalone skills are available): https://learn.chatgpt.com/docs/build-skills (formerly developers.openai.com/codex/skills)
- Skills concept (a folder of instructions and resources centred on SKILL.md): https://developers.openai.com/plugins/concepts/skills
- Skills bundle rules (single top-level folder, one SKILL.md, 50 MB zip, 500 files, 25 MB per file; these are the API limits): https://developers.openai.com/api/docs/guides/tools-skills
- Skills in ChatGPT (upload steps and plans): https://help.openai.com/en/articles/20001066-skills-in-chatgpt. Not verified: the page returned HTTP 403 to automated fetches.
- Knowledge in GPTs (file limits): https://help.openai.com/en/articles/8843948-knowledge-in-gpts. Not verified for the same reason; search summaries state 20 files per GPT.

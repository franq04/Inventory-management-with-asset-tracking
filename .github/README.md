# Copilot Asset Turnover Workflow

Use these files for the bulk employee asset turnover feature:

- `agents/asset-turnover-strict.agent.md` for strict agent-mode implementation
- `prompts/asset-turnover-strict.prompt.md` for a reusable prompt body

Recommended use:
- Use the agent when you want the code changed directly in a secure, transaction-safe way.
- Use the prompt when you want to paste the instructions into Copilot or a custom chat workflow.

Design rules:
- Reuse `asset_movements` as the only movement history source.
- Keep turnover as a specialized batch movement flow.
- Keep procurement separate from live custody tracking.
- Keep physical location on PQS assets, not on employees.
- Use validation, authorization, and one database transaction for the full batch.

Note: keep this setup committed to the repository so the agent mode remains available.

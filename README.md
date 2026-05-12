# README #

This README would normally document whatever steps are necessary to get your application up and running.

### What is this repository for? ###

* Quick summary
* Version
* [Learn Markdown](https://bitbucket.org/tutorials/markdowndemo)

### How do I get set up? ###

* Summary of set up
* Configuration
* Dependencies
* Database configuration
* How to run tests
* Deployment instructions

### Contribution guidelines ###

* Writing tests
* Code review
* Other guidelines

### Who do I talk to? ###

* Repo owner or admin
* Other community or team contact

### VS Code Agent Mode troubleshooting (Copilot) ###

If Agent Mode fails with an error like:

`Request Failed: 400 {"error":{"message":"model \"gpt-5.2-codex\" is not accessible via the /chat/completions endpoint","code":"unsupported_api_for_model"}}`

the issue is a client-side model/endpoint mismatch in VS Code (not an application code failure in this repository).

#### Root cause ####

The selected chat/agent model is being sent to an endpoint it does not support (`/chat/completions`), so the request is rejected.

#### Resolution (step-by-step) ####

1. Update **VS Code** and the **GitHub Copilot** + **GitHub Copilot Chat** extensions to latest.
2. Open Command Palette (`Ctrl+Shift+P` / `Cmd+Shift+P`) and run **Developer: Reload Window**.
3. Open Copilot settings and switch Agent/Chat model from `gpt-5.2-codex` to a model that your Copilot client lists as chat-compatible.
4. Retry Agent Mode in this repository.
5. If it still fails, sign out/in of GitHub Copilot in VS Code and retry.
6. Check **View → Output → GitHub Copilot** (and **GitHub Copilot Chat**) for routing/model details.

#### Notes for this repository ####

* This repository does not force Copilot model routing server-side.
* Model and endpoint compatibility is controlled by your local VS Code + Copilot client configuration.

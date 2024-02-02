## Working on your project
1. For new feature development, fork `develop` branch into a new branch with one of the two patterns:
	* `feature/{feature-description}`
2. Commit often, and write descriptive commit messages, so it's easier to follow steps taken when reviewing.
3. Push this branch to the repo and create pull request into `develop` to get feedback, with the format `feature/{feature-description}` - "Short descriptive title".
4. Iterate as needed.
5. Make sure that "All checks have passed" on GitHub Actions (or another one in case you are not using Actions) and status is green.
6. When PR is approved, it will be squashed & merged, into `develop` and later merged into `master` for deployment.

Note: You can find git flow detail example [here](https://danielkummer.github.io/git-flow-cheatsheet).

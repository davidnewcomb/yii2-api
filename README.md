# Yii 2 Podium API

![Build](https://github.com/yii-podium/yii2-api/workflows/Tests/badge.svg)

**[Work In Progress]**

Podium is divided into the components (not to be mistaken by Yii's components although these are implemented as them) 
that take care of main aspects of forum structure. Each component is responsible for the actions (again, not Yii's 
actions) concerning its aspect, and these actions are implemented as services that operate on repositories. As for the 
repositories - these are objects that know about the storage of data they can handle and how to work with them.

There are some rules:
 - only repositories know about the storage,
 - each repository knows how to handle one single storage unit and not more,
 - components operate on repositories, not on identifiers.

TODOs:
 - [x] Components
 - [x] PHPStan
 - [ ] Add getRepository in components
 - [X] Move repo config from services to components
 - [ ] Load repository after creating new AR
 - [ ] Split Podium and AR Podium
 - [X] Move Podium to separate Github repo
 - [ ] Unit tests
 - [ ] Functional tests
 - [ ] Infection

When API is ready, I'll start preparing the client.
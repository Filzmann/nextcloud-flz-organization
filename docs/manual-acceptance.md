# Manual acceptance

Field | Entry
--- | ---
App version | 
Nextcloud version | 
Source commit | 

ID | Check | Result | Why/Beleg/Abweichung
--- | --- | --- | ---
ORG-01 | Fresh install exposes an invalid empty organization snapshot until explicit configuration. | [ ] PASS [ ] FAIL | 
ORG-02 | Compatible V1 consumers load without LocalBase enabled. | [ ] PASS [ ] FAIL | 
ORG-03 | Missing, disabled, and incompatible provider states do not cause a fatal error. | [ ] PASS [ ] FAIL | 
ORG-04 | A malformed organization definition denies authorization and changes no state. | [ ] PASS [ ] FAIL | 
ORG-05 | Holiday success, stale-cache fallback, and unavailable first fetch are distinguishable. | [ ] PASS [ ] FAIL | 
ORG-06 | The current and next two holiday years are registered/refreshed by the background job. | [ ] PASS [ ] FAIL | 
ORG-07 | Deactivation, removal, and reinstall follow the documented development lifecycle. | [ ] PASS [ ] FAIL | 

This file is an unfilled repeatable instruction. Local entries are not release evidence.

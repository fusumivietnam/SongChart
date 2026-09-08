# Lộ trình sản phẩm

Trạng thái: định hướng active/future. `docs/project/engineering/stage-plan.json` sở hữu stage đang triển khai; generated state + Git/GitHub sở hữu projection/runtime facts; `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã accepted.

## Stage 21 — Editorial Admin UX

Chuyển Admin từ technical-console-first sang task-oriented editorial operations: Nội dung, Cần xử lý, Nguồn dữ liệu và Hệ thống. Technical diagnostics vẫn tồn tại nhưng không chi phối information architecture.

## Stage 22 — Production Vertical Closure

Đóng các giao điểm subsystem thành product flow có bằng chứng: provider evidence → canonical identity/admission → deterministic chart contract/snapshot provenance → canonical public read path, đồng thời bảo đảm correction propagation và exact-tree verification. Không tạo chart/listener/popularity giả để đạt closure. Design-system authority vẫn được duy trì bởi `docs/ui/` và các production component/browser contracts hiện có; Stage 22 không đổi frontend stack chỉ để đạt fidelity.

## Stage 23 — Public Product UX

Hoàn thiện discovery/search/artist/recording/work/release/charts/collections/credits/mobile experience. Chart UX Stage 23 tiêu thụ persisted chart read contract từ Stage 22 thay vì tự tạo ranking semantics. Đánh giá Cloudflare Pages/Workers và search-engine integration chỉ ở nơi public UX/traffic chứng minh nhu cầu.

## Stage 24 — Operational Intelligence

Hợp nhất Pulse/Horizon/runtime telemetry với external observability khi justified; xây scale scorecard từ latency, saturation, queue wait, DB connections/query latency, cache hit, provider failures và search visibility. Grafana Cloud/MCP là candidate, không phải prerequisite.

## Stage 25 — Global Delivery & Scale

Đánh giá CDN/cache policy, Workers, Hyperdrive, replicas, regional strategy, load balancing, circuit breaking và external APM dựa trên metrics. Envoy chỉ được xem lại khi xuất hiện multi-service/gRPC/mTLS/multi-region hoặc traffic-control requirement tương ứng.

## AI sản phẩm

Visitor-facing AI assistant không nằm trong development tooling. Chỉ triển khai khi product evidence cho thấy conversational discovery tạo giá trị; bắt đầu read-only, retrieval-grounded, dùng governed SongChart read models/APIs, có citation/provenance và cost controls. OpenRouter/Hermes/OpenCode/9Router không phải production dependency hiện tại.

## Repository simplification

Legacy `STAGE_*` docs được chuyển đổi theo `docs/project/engineering/documentation-consolidation-contract.json`: durable rule → current owner, chronology → Development History, guarded failure → regression ledger, reusable lesson → AI learning ledger, exact retired text → Git/PR history. Không tạo một thư mục archive mới chứa nguyên đống file cũ.

## Quy tắc roadmap

- Mỗi stage bắt đầu từ accepted use case + task contract.
- Provider data là evidence/reference, không phải schema authority.
- Scale/infrastructure adoption phải chỉ ra metric/threshold và expected improvement.
- Completed work rời roadmap và đi vào Development History.
- Current state không được copy vào roadmap.

import { Router } from 'express'

const router = Router()

// GET /api/dns
router.get('/', (req, res) => {
  res.json({
    message: 'DNS Record endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/dns
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'DNS Record created successfully',
    data: req.body,
  })
})

// GET /api/dns/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'DNS Record details',
    data: { id: req.params.id },
  })
})

// PUT /api/dns/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'DNS Record updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/dns/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'DNS Record deleted successfully',
    id: req.params.id,
  })
})

export default router

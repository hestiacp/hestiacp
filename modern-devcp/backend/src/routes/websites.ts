import { Router } from 'express'

const router = Router()

// GET /api/websites
router.get('/', (req, res) => {
  res.json({
    message: 'Website endpoint',
    data: [],
    total: 0,
  })
})

// POST /api/websites
router.post('/', (req, res) => {
  res.status(201).json({
    message: 'Website created successfully',
    data: req.body,
  })
})

// GET /api/websites/:id
router.get('/:id', (req, res) => {
  res.json({
    message: 'Website details',
    data: { id: req.params.id },
  })
})

// PUT /api/websites/:id
router.put('/:id', (req, res) => {
  res.json({
    message: 'Website updated successfully',
    data: { id: req.params.id, ...req.body },
  })
})

// DELETE /api/websites/:id
router.delete('/:id', (req, res) => {
  res.json({
    message: 'Website deleted successfully',
    id: req.params.id,
  })
})

export default router
